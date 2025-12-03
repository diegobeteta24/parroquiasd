<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MassInstance;
use App\Models\Priest;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use Illuminate\Support\Str;

class SpecialMassController extends Controller
{
    public function index()
    {
        $masses = MassInstance::with('priest')
            ->special()
            ->where('special_category', '<>', 'rosario')
            ->orderBy('starts_at','desc')
            ->paginate(20);
        $duplicates = MassInstance::query()
            ->where('is_special', true)
            ->where('special_category', '<>', 'rosario')
            ->selectRaw('starts_at, COUNT(*) as c')
            ->groupBy('starts_at')
            ->having('c', '>', 1)
            ->orderBy('starts_at', 'desc')
            ->get();
        return view('admin.special-masses.index', compact('masses','duplicates'));
    }

    public function create()
    {
        $priests = Priest::orderBy('name')->get(['id','name']);
        return view('admin.special-masses.create', compact('priests'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'starts_at' => ['required','date', Rule::unique('mass_instances','starts_at')->where(fn($q) => $q->where('is_special', true))],
            'capacity' => ['required','integer','min:1','max:200'],
            'special_category' => ['required', Rule::in(['bautismo','confirmacion','primera_comunion','matrimonio','otra'])],
            'priest_id' => ['nullable','integer', Rule::exists('priests','id')],
            'notes' => ['nullable','string','max:2000'],
            'details' => ['nullable','array'],
            'reservation_amount' => ['nullable','numeric','min:0','max:1000000'],
            // The following are validated conditionally by category below
        ]);

        // Conditional validation and normalization for details by category
        $details = [];
        switch ($data['special_category']) {
            case 'bautismo':
                $validated = $request->validate([
                    'details.child_name' => ['required','string','min:3'],
                    'details.father_name' => ['nullable','string'],
                    'details.mother_name' => ['nullable','string'],
                    'details.godparents' => ['nullable','string'],
                    'details.birth_date' => ['nullable','date'],
                ]);
                $details = $validated['details'] ?? [];
                break;
            case 'confirmacion':
                $validated = $request->validate([
                    'details.candidate_name' => ['required','string','min:3'],
                    'details.sponsor' => ['nullable','string'],
                    'details.parish' => ['nullable','string'],
                ]);
                $details = $validated['details'] ?? [];
                break;
            case 'primera_comunion':
                $validated = $request->validate([
                    'details.group' => ['nullable','string'],
                    'details.catechist' => ['nullable','string'],
                    'details.school' => ['nullable','string'],
                ]);
                $details = $validated['details'] ?? [];
                break;
            case 'matrimonio':
                $validated = $request->validate([
                    'details.bride_name' => ['required','string','min:3'],
                    'details.groom_name' => ['required','string','min:3'],
                    'details.witnesses' => ['nullable','string'],
                    'details.civil_marriage_date' => ['nullable','date'],
                ]);
                $details = $validated['details'] ?? [];
                break;
            case 'otra':
            default:
                // No strict details required
                $details = (array) $request->input('details', []);
        }

        // Optional: create or edit a priest via form if provided
        $priestId = $data['priest_id'] ?? null;
        $newName = trim((string) $request->input('new_priest_name', ''));
        if (!$priestId && $newName !== '') {
            $normalized = mb_strtolower(trim($newName));
            $existing = Priest::query()->whereRaw('LOWER(TRIM(name)) = ?', [$normalized])->first();
            if ($existing) {
                $priestId = $existing->id;
                if ($request->boolean('edit_selected')) {
                    $existing->fill([
                        'name' => $newName, // keep case entered
                        'phone' => $request->input('new_priest_phone'),
                        'email' => $request->input('new_priest_email'),
                    ])->save();
                }
            } else {
                $created = Priest::create([
                    'name' => $newName,
                    'phone' => $request->input('new_priest_phone'),
                    'email' => $request->input('new_priest_email'),
                ]);
                $priestId = $created->id;
            }
        } elseif ($priestId && $request->boolean('edit_selected')) {
            if ($p = Priest::find($priestId)) {
                $p->fill([
                    'name' => $newName !== '' ? $newName : $p->name,
                    'phone' => $request->input('new_priest_phone'),
                    'email' => $request->input('new_priest_email'),
                ])->save();
            }
        }

        $starts = Carbon::parse($data['starts_at'], config('app.timezone'));
        // Business rule: Special masses (non-rosary) only on Saturdays at allowed times
        $allowedTimes = ['08:30','10:00','12:00','15:00','16:30'];
        if ($starts->dayOfWeek !== Carbon::SATURDAY) {
            return back()->withErrors(['starts_at' => 'Las misas especiales solo pueden agendarse en día sábado.'])->withInput();
        }
        if (!in_array($starts->format('H:i'), $allowedTimes, true)) {
            return back()->withErrors(['starts_at' => 'Horario inválido. Horarios permitidos: 08:30, 10:00, 12:00, 15:00 y 16:30.'])->withInput();
        }

        // Un solo evento especial por horario (independiente de la categoría)
        $exists = MassInstance::where('is_special', true)
            ->where('starts_at', $starts)
            ->exists();
        if ($exists) {
            return back()->withErrors(['starts_at' => 'Ya existe una misa especial en ese horario.'])->withInput();
        }

        $mass = MassInstance::create([
            'starts_at' => $starts,
            'capacity' => (int)$data['capacity'],
            'occupied' => 0,
            'status' => 'scheduled',
            'source' => 'override',
            'is_special' => true,
            'special_category' => $data['special_category'],
            'priest_id' => $priestId,
            'notes' => $data['notes'] ?? null,
            'special_details' => $details,
            'reservation_amount' => $data['reservation_amount'] ?? null,
        ]);

        return redirect()->route('admin.special-masses.show', $mass)->with('status','Misa especial creada.');
    }

    public function show(MassInstance $special_mass)
    {
        abort_unless($special_mass->is_special && $special_mass->special_category !== 'rosario', 404);
        $special_mass->load(['intentions.dedicatees','priest']);
        $histories = $special_mass->histories()->with('user')->latest()->get();
        return view('admin.special-masses.show', ['mass' => $special_mass, 'histories' => $histories]);
    }

    public function edit(MassInstance $special_mass)
    {
        abort_unless($special_mass->is_special && $special_mass->special_category !== 'rosario', 404);
    $priests = Priest::orderBy('name')->get(['id','name']);
    return view('admin.special-masses.edit', ['mass' => $special_mass, 'priests' => $priests]);
    }

    public function update(Request $request, MassInstance $special_mass)
    {
        abort_unless($special_mass->is_special && $special_mass->special_category !== 'rosario', 404);
        $data = $request->validate([
            'starts_at' => ['required','date', Rule::unique('mass_instances','starts_at')->where(fn($q) => $q->where('is_special', true))->ignore($special_mass->id, 'id')],
            'capacity' => ['required','integer','min:1','max:200'],
            'special_category' => ['required', Rule::in(['bautismo','confirmacion','primera_comunion','matrimonio','otra'])],
            'priest_id' => ['nullable','integer', Rule::exists('priests','id')],
            'notes' => ['nullable','string','max:2000'],
            'details' => ['nullable','array'],
            'reservation_amount' => ['nullable','numeric','min:0','max:1000000'],
            'justification' => ['required','string','min:6'],
        ]);

        // Conditional details validation as above
        $details = [];
        switch ($data['special_category']) {
            case 'bautismo':
                $validated = $request->validate([
                    'details.child_name' => ['required','string','min:3'],
                    'details.father_name' => ['nullable','string'],
                    'details.mother_name' => ['nullable','string'],
                    'details.godparents' => ['nullable','string'],
                    'details.birth_date' => ['nullable','date'],
                ]);
                $details = $validated['details'] ?? [];
                break;
            case 'confirmacion':
                $validated = $request->validate([
                    'details.candidate_name' => ['required','string','min:3'],
                    'details.sponsor' => ['nullable','string'],
                    'details.parish' => ['nullable','string'],
                ]);
                $details = $validated['details'] ?? [];
                break;
            case 'primera_comunion':
                $validated = $request->validate([
                    'details.group' => ['nullable','string'],
                    'details.catechist' => ['nullable','string'],
                    'details.school' => ['nullable','string'],
                ]);
                $details = $validated['details'] ?? [];
                break;
            case 'matrimonio':
                $validated = $request->validate([
                    'details.bride_name' => ['required','string','min:3'],
                    'details.groom_name' => ['required','string','min:3'],
                    'details.witnesses' => ['nullable','string'],
                    'details.civil_marriage_date' => ['nullable','date'],
                ]);
                $details = $validated['details'] ?? [];
                break;
            case 'otra':
            default:
                $details = (array) $request->input('details', []);
        }

        // Handle priest create/edit same as in store
        $priestId = $data['priest_id'] ?? null;
        $newName = trim((string) $request->input('new_priest_name', ''));
        if (!$priestId && $newName !== '') {
            $normalized = mb_strtolower(trim($newName));
            $existing = Priest::query()->whereRaw('LOWER(TRIM(name)) = ?', [$normalized])->first();
            if ($existing) {
                $priestId = $existing->id;
                if ($request->boolean('edit_selected')) {
                    $existing->fill([
                        'name' => $newName,
                        'phone' => $request->input('new_priest_phone'),
                        'email' => $request->input('new_priest_email'),
                    ])->save();
                }
            } else {
                $created = Priest::create([
                    'name' => $newName,
                    'phone' => $request->input('new_priest_phone'),
                    'email' => $request->input('new_priest_email'),
                ]);
                $priestId = $created->id;
            }
        } elseif ($priestId && $request->boolean('edit_selected')) {
            if ($p = Priest::find($priestId)) {
                $p->fill([
                    'name' => $newName !== '' ? $newName : $p->name,
                    'phone' => $request->input('new_priest_phone'),
                    'email' => $request->input('new_priest_email'),
                ])->save();
            }
        }
        $starts = Carbon::parse($data['starts_at'], config('app.timezone'));
        $allowedTimes = ['08:30','10:00','12:00','15:00','16:30'];
        if ($starts->dayOfWeek !== Carbon::SATURDAY) {
            return back()->withErrors(['starts_at' => 'Las misas especiales solo pueden agendarse en día sábado.'])->withInput();
        }
        if (!in_array($starts->format('H:i'), $allowedTimes, true)) {
            return back()->withErrors(['starts_at' => 'Horario inválido. Horarios permitidos: 08:30, 10:00, 12:00, 15:00 y 16:30.'])->withInput();
        }

        // Un solo evento especial por horario (independiente de la categoría)
        $exists = MassInstance::where('id','<>',$special_mass->id)
            ->where('is_special', true)
            ->where('starts_at', $starts)
            ->exists();
        if ($exists) {
            return back()->withErrors(['starts_at' => 'Conflicto: ya existe una misa especial en ese horario.'])->withInput();
        }

        $before = $special_mass->only(['starts_at','capacity','special_category','priest_id','notes','special_details','reservation_amount']);
        $special_mass->update([
            'starts_at' => $starts,
            'capacity' => (int)$data['capacity'],
            'special_category' => $data['special_category'],
            'priest_id' => $priestId,
            'notes' => $data['notes'] ?? null,
            'special_details' => $details,
            'reservation_amount' => $data['reservation_amount'] ?? null,
        ]);

        $after = $special_mass->only(['starts_at','capacity','special_category','priest_id','notes','special_details','reservation_amount']);
        $special_mass->histories()->create([
            'user_id' => $request->user()?->id,
            'action' => 'updated',
            'justification' => $data['justification'],
            'changes' => ['before' => $before, 'after' => $after],
        ]);

        return redirect()->route('admin.special-masses.show', $special_mass)->with('status','Actualizado.');
    }

    public function destroy(MassInstance $special_mass)
    {
        abort_unless($special_mass->is_special && $special_mass->special_category !== 'rosario', 404);
        request()->validate(['justification' => ['required','string','min:6']]);
        $special_mass->histories()->create([
            'user_id' => request()->user()?->id,
            'action' => 'deleted',
            'justification' => request('justification'),
            'changes' => null,
        ]);
        $special_mass->delete();
        return redirect()->route('admin.special-masses.index')->with('status','Eliminado.');
    }
}
