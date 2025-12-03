<?php

namespace App\Console\Commands;

use App\Models\MassInstance;
use App\Models\MassOverride;
use App\Models\MassTimeTemplate;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MaterializeMasses extends Command
{
    protected $signature = 'masses:materialize {--until=} {--days=} {--tz=America/Guatemala}';
    protected $description = 'Materialize mass instances';

    public function handle(): int
    {
        $tz = $this->option('tz');
        $start = Carbon::now($tz)->startOfDay();

        // Support --until=YYYY-MM-DD; default to last day of next year if not provided.
        $untilOpt = $this->option('until');
        $daysOpt = $this->option('days');
        if ($untilOpt) {
            try {
                $end = Carbon::parse($untilOpt, $tz)->endOfDay();
            } catch (\Throwable $e) {
                $this->error('Invalid --until date format. Use YYYY-MM-DD.');
                return self::FAILURE;
            }
        } elseif ($daysOpt !== null && $daysOpt !== '') {
            $days = (int) $daysOpt;
            if ($days < 0) { $days = 0; }
            $end = $start->clone()->addDays($days);
        } else {
            // Last day of next year
            $end = $start->clone()->addYear()->endOfYear();
        }

        $templates = MassTimeTemplate::where('active', true)->get()->groupBy('dow');
        $overrides = MassOverride::whereBetween('date', [$start->toDateString(), $end->toDateString()])->get()->groupBy('date');

        $created = 0; $updated = 0;
        DB::beginTransaction();
        try {
            foreach (CarbonPeriod::create($start, $end) as $day) {
                $dateStr = $day->toDateString();
                // Carbon supplies dayOfWeekIso (1=Mon..7=Sun)
                $dow = $day->dayOfWeekIso; // 1..7
                $dayTemplates = $templates->get($dow, collect());
                $dayTimes = $dayTemplates->map(fn($t)=>[
                    'time'=>$t->time,'capacity'=>$t->capacity,'source'=>'template'
                ])->keyBy('time');
                if ($overrides->has($dateStr)) {
                    foreach ($overrides->get($dateStr) as $ov) {
                        if ($ov->action==='close_day') { $dayTimes = collect(); break; }
                    }
                    foreach ($overrides->get($dateStr) as $ov) {
                        if ($ov->action==='remove' && $ov->time) { $dayTimes->forget($ov->time); }
                        if ($ov->action==='add' && $ov->time) {
                            $dayTimes->put($ov->time,[
                                'time'=>$ov->time,
                                'capacity'=>$ov->capacity ?? $dayTimes->get($ov->time)['capacity'] ?? 10,
                                'source'=>'override'
                            ]);
                        }
                    }
                }
                foreach ($dayTimes as $time => $meta) {
                    // Guardar en hora local de la app
                    $startsAt = Carbon::parse($dateStr.' '.$time, $tz);
                    $payload = ['starts_at'=>$startsAt,'capacity'=>$meta['capacity'],'source'=>$meta['source']];
                    // Only consider ordinary masses here
                    $existing = MassInstance::where('starts_at',$startsAt)->where('is_special', false)->first();
                    if ($existing) { $existing->fill($payload); if ($existing->isDirty()) { $existing->save(); $updated++; } }
                    else { MassInstance::create($payload + ['status'=>'scheduled','occupied'=>0,'is_special'=>false,'special_category'=>null]); $created++; }
                }
            }
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->error($e->getMessage());
            return self::FAILURE;
        }
        $this->info("Mass instances created: $created; updated: $updated. Range: ".$start->toDateString()." → ".$end->toDateString());
        return self::SUCCESS;
    }
}
