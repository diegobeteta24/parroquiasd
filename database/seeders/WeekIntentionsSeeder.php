<?php

namespace Database\Seeders;

use App\Models\Intention;
use App\Models\IntentionDedicatee;
use App\Models\MassInstance;
use App\Models\MassTimeTemplate;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class WeekIntentionsSeeder extends Seeder
{
    public function run(): void
    {
        // Create MassInstances for the next 7 days from active templates
        $tz = config('app.timezone');
        $today = Carbon::now($tz)->startOfDay();

        $templatesByDow = MassTimeTemplate::query()->where('active', true)->get()->groupBy('dow');

        DB::transaction(function () use ($today, $templatesByDow) {
            for ($i = 0; $i < 7; $i++) {
                $date = $today->copy()->addDays($i);
                $isoDow = (int) $date->isoWeekday(); // 1..7
                $templates = $templatesByDow->get($isoDow, collect());
                foreach ($templates as $tpl) {
                    // Build starts_at by combining date + tpl->time (HH:MM)
                    $startsAt = Carbon::parse($date->toDateString() . ' ' . $tpl->time, $date->timezone);

                    // Ensure uniqueness based on starts_at
                    $mass = MassInstance::firstOrCreate(
                        ['starts_at' => $startsAt],
                        [
                            'capacity' => $tpl->capacity,
                            'occupied' => 0,
                            'status' => 'scheduled',
                            'source' => 'template',
                        ]
                    );

                    // Create 1-3 intentions for the mass, not exceeding capacity
                    $free = max(0, $mass->capacity - $mass->occupied);
                    $toCreate = $free > 0 ? min($free, rand(1, 3)) : 0;
                    for ($n = 0; $n < $toCreate; $n++) {
                        /** @var Intention $int */
                        $int = Intention::factory()->create([
                            'mass_instance_id' => $mass->id,
                        ]);
                        // 0-1 dedicatario por intención
                        $dedCount = rand(0, 1);
                        if ($dedCount === 1) {
                            IntentionDedicatee::factory()->create([
                                'intention_id' => $int->id,
                            ]);
                        }
                    }

                    // Update occupied
                    $mass->occupied = $mass->intentions()->count();
                    $mass->save();
                }
            }
        });
    }
}
