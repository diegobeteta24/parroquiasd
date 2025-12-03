<?php

namespace App\Console\Commands;

use App\Models\MassInstance;
use Carbon\Carbon;
use Illuminate\Console\Command;

class PopulateOctober2025 extends Command
{
    protected $signature = 'calendar:october-2025 {--tz=America/Guatemala}';
    protected $description = 'Añade (idempotente) las misas y rosarios del mes de octubre 2025 según el programa parroquial.';

    public function handle(): int
    {
        $tz = $this->option('tz') ?: config('app.timezone', 'America/Guatemala');
        $start = Carbon::create(2025, 10, 1, 0, 0, 0, $tz)->startOfDay();
        $end   = Carbon::create(2025, 10, 31, 23, 59, 59, $tz)->endOfDay();

        $createdMasses = 0; $skippedMasses = 0; $createdRosaries = 0; $skippedRosaries = 0;

        $cursor = $start->copy();
        while ($cursor->lte($end)) {
            $dow = $cursor->dayOfWeekIso; // 1=Mon .. 7=Sun

            // Horarios de misa
            // Lunes a sábado comparten el mismo bloque en el programa
            if ($dow >= 1 && $dow <= 6) {
                $massTimes = ['06:00','07:00','08:00','10:00','12:00','16:00','19:00'];
            } else { // Domingo
                $massTimes = ['06:00','08:00','10:00','12:00','18:00'];
            }

            foreach ($massTimes as $t) {
                $startsAt = Carbon::parse($cursor->toDateString().' '.$t, $tz);
                $exists = MassInstance::where('starts_at', $startsAt)
                    ->where('is_special', false)
                    ->exists();
                if ($exists) { $skippedMasses++; continue; }
                MassInstance::create([
                    'starts_at' => $startsAt,
                    'capacity'  => 10,
                    'occupied'  => 0,
                    'status'    => 'scheduled',
                    'source'    => 'override', // especificación de octubre
                    'is_special'=> false,
                    'special_category' => null,
                ]);
                $createdMasses++;
            }

            // Rosarios del mes del Rosario
            if ($dow >= 1 && $dow <= 6) {
                // Lunes a sábado
                $rosaryTimes = ['05:15','09:15','15:00','17:45'];
            } else { // Domingo
                $rosaryTimes = ['05:15','16:00','19:00'];
            }

            // Primer y segundo sábado: Rosario de antorchas 20:30
            if ($dow === 6) {
                $day = (int)$cursor->format('j');
                if ($day <= 14) { // sábados 4 y 11 de oct 2025
                    $rosaryTimes[] = '20:30';
                }
            }

            foreach ($rosaryTimes as $t) {
                $startsAt = Carbon::parse($cursor->toDateString().' '.$t, $tz);
                $exists = MassInstance::where('starts_at', $startsAt)
                    ->where('is_special', true)
                    ->where('special_category', 'rosario')
                    ->exists();
                if ($exists) { $skippedRosaries++; continue; }
                MassInstance::create([
                    'starts_at' => $startsAt,
                    'capacity'  => 0,
                    'occupied'  => 0,
                    'status'    => 'scheduled',
                    'source'    => 'override',
                    'is_special'=> true,
                    'special_category' => 'rosario',
                    'notes'     => $t === '20:30' ? 'Rosario de antorchas (Atrio)' : null,
                ]);
                $createdRosaries++;
            }

            $cursor->addDay();
        }

        $this->info("Misas creadas: {$createdMasses}; ya existentes: {$skippedMasses}. Rosarios creados: {$createdRosaries}; ya existentes: {$skippedRosaries}.");
        $this->info("Rango: {$start->toDateString()} → {$end->toDateString()} (TZ {$tz})");
        return self::SUCCESS;
    }
}
