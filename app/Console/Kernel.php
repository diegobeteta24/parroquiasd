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
        \App\Console\Commands\SendBackupToRelayCommand::class,
    ];
    protected function schedule(Schedule $schedule): void
    {
        $defineSchedule = require base_path('routes/schedule.php');
        $defineSchedule($schedule);
    }

    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');
    }
}
