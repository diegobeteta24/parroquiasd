<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected $commands = [
        \App\Console\Commands\HealIntentionGroups::class,
        \App\Console\Commands\PopulateOctober2025::class,
        \App\Console\Commands\IntentionsPurge::class,
        \App\Console\Commands\SimulateRecurrenteWebhook::class,
        \App\Console\Commands\PurgeTodayCertificates::class,
    ];
    protected function schedule(Schedule $schedule): void
    {
        $schedule->command('masses:materialize --days=365 --tz=America/Guatemala')
            ->timezone('America/Guatemala')
            ->dailyAt('02:05');

        // Daily DB backup at 7:00 PM Guatemala
        $schedule->command('backup:database --keep=14')
            ->timezone('America/Guatemala')
            ->dailyAt('19:00');
    }

    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');
    }
}
