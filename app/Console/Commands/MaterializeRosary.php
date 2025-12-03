<?php

namespace App\Console\Commands;

use App\Models\MassInstance;
use Carbon\Carbon;
use Illuminate\Console\Command;

class MaterializeRosary extends Command
{
    protected $signature = 'rosary:materialize {--until=} {--tz=America/Guatemala}';
    protected $description = 'Crear (idempotente) rezos del rosario todos los días a las 18:30 hasta la fecha indicada (por defecto 2026-12-31).';

    public function handle(): int
    {
        $tz = $this->option('tz') ?: 'America/Guatemala';
        $today = Carbon::now($tz)->startOfDay();
        $until = $this->option('until') ? Carbon::parse($this->option('until'), $tz)->endOfDay() : Carbon::create(2026, 12, 31, 23, 59, 59, $tz);

        if ($until->lessThan($today)) {
            $this->warn('La fecha --until es anterior a hoy; nada que hacer.');
            return self::SUCCESS;
        }

        $created = 0; $skipped = 0;
        $cursor = $today->copy();
        while ($cursor->lte($until)) {
            $startsAt = $cursor->copy()->setTime(18, 30);
            $exists = MassInstance::where('starts_at', $startsAt)
                ->where('is_special', true)
                ->where('special_category', 'rosario')
                ->exists();
            if ($exists) { $skipped++; }
            else {
                MassInstance::create([
                    'starts_at' => $startsAt,
                    'capacity' => 0,
                    'occupied' => 0,
                    'status' => 'scheduled',
                    'source' => 'override',
                    'is_special' => true,
                    'special_category' => 'rosario',
                ]);
                $created++;
            }
            $cursor->addDay();
        }

        $this->info("Rosarios creados: {$created}; existentes: {$skipped}. Rango: {$today->toDateString()} → {$until->toDateString()}");
        return self::SUCCESS;
    }
}
