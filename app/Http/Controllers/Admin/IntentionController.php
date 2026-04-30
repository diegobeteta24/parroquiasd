<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreIntentionRequest;
use App\Http\Requests\Admin\UpdateIntentionRequest;
use App\Models\Image;
use App\Models\Intention;
use App\Models\MassInstance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Str;

class IntentionController extends Controller
{
    public function create(MassInstance $mass)
    {
        // Preload next candidate masses for optional manual selection
        $isRosary = $mass->is_special && $mass->special_category === 'rosario';
        $maxRepetitions = $this->maxRepetitions();
        $allowsOverflow = $this->allowsOverflow();
        $nextMasses = $this->upcomingCandidatesQuery($mass)
            ->limit($maxRepetitions)
            ->get();

        return view('admin.intentions.create', [
            'mass' => $mass,
            'nextMasses' => $nextMasses,
            'isRosary' => $isRosary,
            'maxRepetitions' => $maxRepetitions,
            'ignoreCapacity' => $allowsOverflow,
        ]);
    }

    public function edit(Intention $intention)
    {
        $intention->load('mass','dedicatee');
        $mass = $intention->mass;
        $isRosary = $mass->is_special && $mass->special_category === 'rosario';
        $maxRepetitions = $this->maxRepetitions();
        $allowsOverflow = $this->allowsOverflow();
        $nextMasses = $this->upcomingCandidatesQuery($mass)
            ->limit($maxRepetitions)
            ->get();
        // Load siblings (repetitions) including current
        $siblings = collect();
        if ($intention->group_code) {
            $siblings = Intention::query()
                ->where('group_code', $intention->group_code)
                ->whereKeyNot($intention->id)
                ->with('mass')
                ->get()
                ->sortBy(fn($i) => optional($i->mass)->starts_at)
                ->values();
        }

        return view('admin.intentions.edit', [
            'intention' => $intention,
            'nextMasses' => $nextMasses,
            'isRosary' => $isRosary,
            'siblings' => $siblings,
            'maxRepetitions' => $maxRepetitions,
            'ignoreCapacity' => $allowsOverflow,
        ]);
    }

    public function store(StoreIntentionRequest $request, MassInstance $mass)
    {
        // Prevent same-day registrations
        $today = now()->timezone(config('app.timezone'));
        if ($mass->starts_at->isSameDay($today)) {
            return back()->withErrors(['mass' => 'No es posible agendar intenciones para el mismo día.'])->withInput();
        }

        $data = $request->validated();
            $isPrepaid = (bool) ($data['is_prepaid'] ?? false);
        $maxRepetitions = $this->maxRepetitions();

        // Set amount based on type
        $amounts = [ 'rosario' => 30, 'rezada' => 50, 'cantada' => 150 ];
        $amount = $amounts[$data['type']];
        // Batch support: arbitrary repeat count (times) and manual selection of extra masses
        $times = $request->times();
        $extraIds = $request->extraMassIds();
        $enforceCapacity = $this->shouldEnforceCapacity();

        // Enforce extras count cap first
        if ($extraIds->count() > max(0, $times - 1)) {
            return back()->withErrors(['extra_mass_ids' => 'Seleccionó más fechas adicionales que las permitidas para el número de repeticiones.'])->withInput();
        }

        // Start with current mass + extras (validated to be within the allowed window of similar celebrations)
        $targets = collect([$mass]);
        // Enforce that extras can only be from the next allowed masses of the same kind
        if ($extraIds->isNotEmpty()) {
            $allowed = $this->upcomingCandidatesQuery($mass)
                ->limit($maxRepetitions)
                ->pluck('id');
            $filteredExtraIds = $extraIds->intersect($allowed);
            $extras = $filteredExtraIds->isNotEmpty()
                ? MassInstance::whereIn('id', $filteredExtraIds)->orderBy('starts_at')->get()
                : collect();
            $targets = $targets->merge($extras);
        }
        $targets = $targets->unique('id');

        // Fill with next masses until reaching desired total (times), excluding already chosen extras
        $need = max(0, $times - $targets->count());
        if ($need > 0) {
            $excludeIds = $targets->pluck('id');
            $next = $this->upcomingCandidatesQuery($mass)
                ->whereNotIn('id', $excludeIds)
                ->take($need)
                ->get();
            $targets = $targets->merge($next);
        }
        // Final safety: sort by date (current first) and clamp to exact 'times'
        $targets = $targets->sortBy(fn($m) => $m->id === $mass->id ? $mass->starts_at->subSecond() : $m->starts_at)->values();
        if ($targets->count() > $times) {
            $targets = $targets->take($times);
        }

        $created = 0; $skipped = 0; $skippedReasons = [];

        \DB::transaction(function () use ($targets, $data, $amount, $isPrepaid, $today, &$created, &$skipped, &$skippedReasons, $request, $enforceCapacity) {
            $firstReceiptImage = null;
            $batchGroup = (string) Str::uuid();

            foreach ($targets as $tMass) {
                // Skip same-day masses
                if ($tMass->starts_at->isSameDay($today)) {
                    $skipped++; $skippedReasons[] = $tMass->starts_at->format('d/m H:i').': mismo día';
                    continue;
                }

                // Capacity check: for rosary treat as unlimited
                $isRosary = $tMass->is_special && $tMass->special_category === 'rosario';
                if ($enforceCapacity && !$isRosary) {
                    $max = $tMass->capacity;
                    $occupied = $tMass->intentions()->count();
                    if ($occupied >= $max) {
                        $skipped++; $skippedReasons[] = $tMass->starts_at->format('d/m H:i').': sin cupo';
                        continue;
                    }
                }

                // Create intention for this mass
                $intention = new Intention();
                $intention->fill([
                    'mass_instance_id' => $tMass->id,
                    'type' => $data['type'],
                    'category' => $data['category'] ?? null,
                    'public_text' => $data['public_text'] ?? null,
                    'donor_name' => $data['donor_name'] ?? null,
                    'phone' => $data['phone'] ?? null,
                    'email' => $data['email'] ?? null,
                    'amount' => $amount,
                    'stipend_amount_gtq' => $data['stipend_amount_gtq'] ?? null,
                    'payment_method' => $data['payment_method'],
                    'payment_ref' => $data['payment_ref'] ?? null,
                    'status' => 'paid',
                    'paid_at' => now(),
                    'channel' => 'counter',
                    'is_prepaid' => $isPrepaid,
                    'code' => strtoupper(bin2hex(random_bytes(4))),
                    'group_code' => $batchGroup,
                ]);
                $intention->save();

                if (!empty($data['dedicatee'])) {
                    $intention->dedicatees()->create(['name' => $data['dedicatee']]);
                }

                // Handle receipt upload once and reuse for all
                if ($request->hasFile('receipt')) {
                    if ($firstReceiptImage === null) {
                        $file = $request->file('receipt');
                        $disk = 'public';
                        $path = $file->store('receipts', $disk);
                        $firstReceiptImage = [
                            'path' => $path,
                            'disk' => $disk,
                            'collection' => 'receipt',
                            'width' => null,
                            'height' => null,
                            'size' => $file->getSize(),
                        ];
                    }
                    $image = new Image($firstReceiptImage);
                    $intention->receipt()->save($image);
                }

                // Update occupied for this mass
                $tMass->occupied = $tMass->intentions()->count();
                $tMass->save();

                $created++;
            }
        });

        $totalAmount = $amount * max(1, $created);
        $msg = $created === 1
            ? 'Intención registrada correctamente.'
            : "Se registraron {$created} intenciones (Total estimado Q{$totalAmount}).";
        if ($skipped) {
            $msg .= " {$skipped} no se registraron (".implode('; ', array_slice($skippedReasons,0,5)).(count($skippedReasons)>5?'…':'').").";
        }

        // Redirect back to the original mass page
        return redirect()->route('admin.masses.show', $mass)
                ->with('status', $msg)
                ->with('batch_created', $created)
                ->with('batch_total_amount', $totalAmount);
    }

    public function certificate(Intention $intention)
    {
        $intention->load(['mass','dedicatees']);
        $pdf = Pdf::loadView('admin.intentions.certificate', [
            'intention' => $intention,
        ])->setPaper('a4', 'landscape');
        $filename = 'constancia-intencion-'.$intention->mass->starts_at->format('Ymd').'-'.$intention->id.'.pdf';
        return $pdf->stream($filename);
    }

    


public function update(UpdateIntentionRequest $request, Intention $intention)
{
    $mass = $intention->mass;
    $data = $request->validated();
    $enforceCapacity = $this->shouldEnforceCapacity();
    $maxRepetitions = $this->maxRepetitions();
    $snapshotKeys = [
        'type',
        'category',
        'public_text',
        'donor_name',
        'phone',
        'email',
        'payment_method',
        'amount',
        'is_prepaid',
        'stipend_amount_gtq',
        'payment_ref',
    ];
    $before = $intention->only($snapshotKeys);

    $amounts = [ 'rosario' => 30, 'rezada' => 50, 'cantada' => 150 ];
    $isPrepaid = $request->boolean('is_prepaid');

    $intention->fill([
        'type' => $data['type'],
        'category' => $data['category'] ?? null,
        'public_text' => $data['public_text'] ?? null,
        'donor_name' => $data['donor_name'] ?? null,
        'phone' => $data['phone'] ?? null,
        'email' => $data['email'] ?? null,
        'payment_method' => $data['payment_method'],
        'amount' => $amounts[$data['type']] ?? $intention->amount,
        'stipend_amount_gtq' => $data['stipend_amount_gtq'] ?? null,
        'payment_ref' => $data['payment_ref'] ?? null,
        'is_prepaid' => $isPrepaid,
    ]);

    $mutableStatuses = ['confirmed','paid'];
    if ($isPrepaid && in_array($intention->status, $mutableStatuses, true)) {
        $intention->status = 'paid';
        $intention->paid_at = $intention->paid_at ?? now();
    } elseif (!$isPrepaid && in_array($intention->status, $mutableStatuses, true)) {
        $intention->status = 'confirmed';
        $intention->paid_at = null;
    }

    $intention->save();

    if ($request->hasFile('receipt')) {
        $file = $request->file('receipt');
        $disk = 'public';
        $path = $file->store('receipts', $disk);
        $imageData = [
            'path' => $path,
            'disk' => $disk,
            'collection' => 'receipt',
            'width' => null,
            'height' => null,
            'size' => $file->getSize(),
        ];

        if ($intention->receipt) {
            Storage::disk($intention->receipt->disk)->delete($intention->receipt->path);
            $intention->receipt()->delete();
        }

        $intention->receipt()->create($imageData);
        $intention->load('receipt');
    }

    $name = $data['dedicatee'] ?? null;
    if ($name) {
        $intention->dedicatee()->updateOrCreate([], ['name' => $name]);
    } else {
        $intention->dedicatee()->delete();
    }
    $intention->load('dedicatee');

    $after = $intention->only($snapshotKeys);
    $intention->histories()->create([
        'user_id' => $request->user()?->id,
        'action' => 'updated',
        'justification' => $data['justification'],
        'changes' => ['before' => $before, 'after' => $after],
    ]);

    if ($request->boolean('group_editor')) {
        $baseMass = $intention->mass;
        $isRosary = $baseMass->is_special && $baseMass->special_category === 'rosario';
        $selected = $request->groupMassIds();
        $allowed = ($isRosary
            ? MassInstance::where('is_special', true)->where('special_category','rosario')
            : MassInstance::where('is_special', false))
            ->where('status','scheduled')
            ->where('starts_at','>', $baseMass->starts_at)
            ->orderBy('starts_at')->limit($maxRepetitions)->pluck('id');
        $selected = $selected->intersect($allowed);

        $group = $intention->group_code ?: (string) Str::uuid();
        if (!$intention->group_code) {
            $intention->group_code = $group;
            $intention->save();
        }

        $current = Intention::query()->where('group_code', $group)
            ->whereKeyNot($intention->id)
            ->pluck('mass_instance_id');

        $toCreate = $selected->diff($current);
        $toDelete = $current->diff($selected);

        $today = now()->timezone(config('app.timezone'));
        \DB::transaction(function () use ($toCreate, $toDelete, $intention, $today, $isRosary, $request, $enforceCapacity) {
            if ($toCreate->isNotEmpty()) {
                $masses = MassInstance::whereIn('id', $toCreate)->orderBy('starts_at')->get();
                foreach ($masses as $tMass) {
                    if ($tMass->starts_at->isSameDay($today)) {
                        continue;
                    }
                    if ($enforceCapacity && !$isRosary) {
                        $max = $tMass->capacity;
                        $occupied = $tMass->intentions()->count();
                        if ($occupied >= $max) {
                            continue;
                        }
                    }
                    $new = new Intention();
                    $new->fill([
                        'mass_instance_id' => $tMass->id,
                        'type' => $intention->type,
                        'category' => $intention->category,
                        'public_text' => $intention->public_text,
                        'donor_name' => $intention->donor_name,
                        'phone' => $intention->phone,
                        'email' => $intention->email,
                        'amount' => $intention->amount,
                        'stipend_amount_gtq' => $intention->stipend_amount_gtq,
                        'payment_method' => $intention->payment_method,
                        'payment_ref' => $intention->payment_ref,
                        'status' => 'paid',
                        'paid_at' => $intention->paid_at ?? now(),
                        'channel' => $intention->channel,
                        'is_prepaid' => $intention->is_prepaid,
                        'code' => strtoupper(bin2hex(random_bytes(4))),
                        'group_code' => $intention->group_code,
                    ]);
                    $new->save();
                    if ($intention->dedicatee) {
                        $new->dedicatees()->create(['name' => $intention->dedicatee->name]);
                    }
                    $tMass->occupied = $tMass->intentions()->count();
                    $tMass->save();
                }
            }

            if ($toDelete->isNotEmpty()) {
                $del = Intention::where('group_code', $intention->group_code)
                    ->whereIn('mass_instance_id', $toDelete)
                    ->get();
                foreach ($del as $d) {
                    $d->histories()->create([
                        'user_id' => $request->user()?->id,
                        'action' => 'deleted',
                        'justification' => $request->input('justification'),
                        'changes' => ['by' => 'group_editor'],
                    ]);
                    $massId = $d->mass_instance_id;
                    $d->delete();
                    $m = MassInstance::find($massId);
                    if ($m) {
                        $m->occupied = $m->intentions()->count();
                        $m->save();
                    }
                }
            }
        });
    }

    $created = 0;
    $skipped = 0;
    $skippedReasons = [];
    $times = $request->times();
    $extraIds = $request->extraMassIds();

    if ($extraIds->count() > max(0, $times - 1)) {
        return back()->withErrors(['extra_mass_ids' => 'Seleccionó más fechas adicionales que las permitidas para el número de repeticiones.'])->withInput();
    }
    if ($times > 1 || $extraIds->isNotEmpty()) {
        $baseMass = $intention->mass;
        $amounts = [ 'rosario' => 30, 'rezada' => 50, 'cantada' => 150 ];
        $amount = $amounts[$intention->type] ?? $intention->amount;
        $targets = collect();
        if ($extraIds->isNotEmpty()) {
            if ($baseMass->is_special && $baseMass->special_category === 'rosario') {
                $allowed = MassInstance::where('is_special', true)
                    ->where('special_category', 'rosario')
                    ->where('status','scheduled')
                    ->where('starts_at', '>', $baseMass->starts_at)
                    ->orderBy('starts_at')
                    ->limit($maxRepetitions)
                    ->pluck('id');
            } else {
                $allowed = MassInstance::where('is_special', false)
                    ->where('status','scheduled')
                    ->where('starts_at', '>', $baseMass->starts_at)
                    ->orderBy('starts_at')
                    ->limit($maxRepetitions)
                    ->pluck('id');
            }
            $filteredExtraIds = $extraIds->intersect($allowed);
            $extras = $filteredExtraIds->isNotEmpty()
                ? MassInstance::whereIn('id', $filteredExtraIds)->orderBy('starts_at')->get()
                : collect();
            $targets = $targets->merge($extras);
        }
        $targets = $targets->unique('id');

        $need = max(0, ($times - 1) - $targets->count());
        if ($need > 0) {
            $excludeIds = $targets->pluck('id');
            if ($baseMass->is_special && $baseMass->special_category === 'rosario') {
                $next = MassInstance::where('is_special', true)
                    ->where('special_category','rosario')
                    ->where('status','scheduled')
                    ->where('starts_at','>', $baseMass->starts_at)
                    ->whereNotIn('id', $excludeIds)
                    ->orderBy('starts_at')
                    ->take($need)
                    ->get();
            } else {
                $next = MassInstance::where('is_special', false)
                    ->where('status','scheduled')
                    ->where('starts_at','>', $baseMass->starts_at)
                    ->whereNotIn('id', $excludeIds)
                    ->orderBy('starts_at')
                    ->take($need)
                    ->get();
            }
            $targets = $targets->merge($next);
        }
        $targets = $targets->sortBy('starts_at')->values();
        if ($targets->count() > ($times - 1)) {
            $targets = $targets->take($times - 1);
        }

        $today = now()->timezone(config('app.timezone'));
        \DB::transaction(function () use ($targets, $intention, $amount, $today, &$created, &$skipped, &$skippedReasons, $enforceCapacity) {
            $group = $intention->group_code ?: (string) Str::uuid();
            if (!$intention->group_code) {
                $intention->group_code = $group;
                $intention->save();
            }
            foreach ($targets as $tMass) {
                if ($tMass->starts_at->isSameDay($today)) {
                    $skipped++;
                    $skippedReasons[] = $tMass->starts_at->format('d/m H:i').': mismo día';
                    continue;
                }
                $isRosary = $tMass->is_special && $tMass->special_category === 'rosario';
                if ($enforceCapacity && !$isRosary) {
                    $max = $tMass->capacity;
                    $occupied = $tMass->intentions()->count();
                    if ($occupied >= $max) {
                        $skipped++;
                        $skippedReasons[] = $tMass->starts_at->format('d/m H:i').': sin cupo';
                        continue;
                    }
                }
                $new = new Intention();
                $new->fill([
                    'mass_instance_id' => $tMass->id,
                    'type' => $intention->type,
                    'category' => $intention->category,
                    'public_text' => $intention->public_text,
                    'donor_name' => $intention->donor_name,
                    'phone' => $intention->phone,
                    'email' => $intention->email,
                    'amount' => $amount,
                    'stipend_amount_gtq' => $intention->stipend_amount_gtq,
                    'payment_method' => $intention->payment_method,
                    'payment_ref' => $intention->payment_ref,
                    'status' => 'paid',
                    'paid_at' => $intention->paid_at ?? now(),
                    'channel' => 'counter',
                    'is_prepaid' => $intention->is_prepaid,
                    'code' => strtoupper(bin2hex(random_bytes(4))),
                    'group_code' => $group,
                ]);
                $new->save();
                if ($intention->dedicatee) {
                    $new->dedicatees()->create(['name' => $intention->dedicatee->name]);
                }
                $tMass->occupied = $tMass->intentions()->count();
                $tMass->save();
                $created++;
            }
        });
    }

    $msg = 'Intención actualizada correctamente.';
    if ($created) {
        $unit = ['rosario'=>30,'rezada'=>50,'cantada'=>150][$intention->type] ?? $intention->amount;
        $totalAmount = $unit * $created;
        $msg .= " Se añadieron {$created} adicionales.";
        if ($skipped) {
            $msg .= " {$skipped} no se añadieron (".implode('; ', array_slice($skippedReasons,0,5)).(count($skippedReasons)>5?'…':'').").";
        }
    }

    $moveId = (int) ($data['move_to_mass_id'] ?? 0);
    if ($moveId && $moveId !== $intention->mass_instance_id) {
        $target = MassInstance::find($moveId);
        if (!$target) {
            return back()->withErrors(['move_to_mass_id' => 'Misa destino no encontrada.'])->withInput();
        }
        $today = now()->timezone(config('app.timezone'));
        $isRosaryTarget = $target->is_special && $target->special_category === 'rosario';
        $isRosaryCurrent = $mass->is_special && $mass->special_category === 'rosario';
        if ($isRosaryTarget !== $isRosaryCurrent) {
            return back()->withErrors(['move_to_mass_id' => 'Solo puede mover a una misa del mismo tipo.'])->withInput();
        }
        if ($target->starts_at->isSameDay($today)) {
            return back()->withErrors(['move_to_mass_id' => 'No es posible mover a una misa del mismo día.'])->withInput();
        }
        if ($enforceCapacity && !$isRosaryTarget) {
            $max = $target->capacity;
            $occupied = $target->intentions()->count();
            if ($occupied >= $max) {
                return back()->withErrors(['move_to_mass_id' => 'La misa destino no tiene cupo disponible.'])->withInput();
            }
        }
        \DB::transaction(function () use ($intention, $target, $data) {
            $oldMass = $intention->mass;
            $intention->mass_instance_id = $target->id;
            $intention->save();
            if ($oldMass) {
                $oldMass->occupied = $oldMass->intentions()->count();
                $oldMass->save();
            }
            $target->occupied = $target->intentions()->count();
            $target->save();
            $intention->histories()->create([
                'user_id' => auth()->id(),
                'action' => 'moved',
                'justification' => $data['justification'],
                'changes' => [
                    'from' => optional($oldMass?->starts_at)->format('Y-m-d H:i'),
                    'to' => optional($target->starts_at)->format('Y-m-d H:i'),
                ],
            ]);
        });
        $msg .= ' Se movió la intención a otra misa.';
    }

    return redirect()->route('admin.masses.show', $intention->mass_instance_id)
        ->with('status', $msg);
}

    public function destroy(Request $request, Intention $intention)
    {
        $data = $request->validate([
            'justification' => ['required','string','min:6']
        ]);

        // Log history before delete
        $intention->histories()->create([
            'user_id' => $request->user()?->id,
            'action' => 'deleted',
            'justification' => $data['justification'],
            'changes' => null,
        ]);

        $massId = $intention->mass_instance_id;
        $intention->delete(); // soft delete

        // Recalcular ocupados
        $mass = \App\Models\MassInstance::find($massId);
        if ($mass) {
            $mass->occupied = $mass->intentions()->count();
            $mass->save();
        }

        return redirect()->route('admin.masses.show', $massId)
            ->with('status', 'Intención eliminada (soft-delete). Se registró la justificación.');
    }

    protected function maxRepetitions(): int
    {
        return (int) config('portal.intentions_max_repetitions', 1000);
    }

    protected function allowsOverflow(): bool
    {
        return (bool) config('portal.intentions_admin_ignore_capacity', true);
    }

    protected function shouldEnforceCapacity(): bool
    {
        return !$this->allowsOverflow();
    }

    protected function upcomingCandidatesQuery(MassInstance $mass)
    {
        $query = MassInstance::query()
            ->where('status', 'scheduled')
            ->where('starts_at', '>', $mass->starts_at)
            ->orderBy('starts_at');

        if ($mass->is_special && $mass->special_category === 'rosario') {
            $query->where('is_special', true)
                ->where('special_category', 'rosario');
        } else {
            $query->where('is_special', false);
        }

        return $query;
    }
}
