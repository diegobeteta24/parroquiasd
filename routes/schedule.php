<?php

use Illuminate\Console\Scheduling\Schedule;

return function (Schedule $schedule): void {
    $schedule->command('masses:materialize --days=365 --tz=America/Guatemala')
        ->timezone('America/Guatemala')
        ->dailyAt('02:05');

    $schedule->command('backup:database --keep=14')
        ->timezone('America/Guatemala')
        ->dailyAt('19:00');

    $schedule->command('backup:mail')
        ->timezone('America/Guatemala')
        ->dailyAt('19:10');
};
