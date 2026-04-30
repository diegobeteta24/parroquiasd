<?php

namespace App\Console\Commands;

use App\Jobs\SendBackupToRelay;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;

class SendBackupToRelayCommand extends Command
{
    protected $signature = 'backup:mail {--path= : Absolute path to the backup file}';
    protected $description = 'Send the latest database backup to the Hostinger relay endpoint';

    public function handle(): int
    {
        $path = $this->option('path');
        if (!$path) {
            $path = $this->latestBackupPath();
        }

        if (!$path) {
            $this->error('No backup files found in storage/app/backups.');
            return self::FAILURE;
        }

        if (!is_file($path)) {
            $this->error('Backup file does not exist: '.$path);
            return self::FAILURE;
        }

        $this->info('Sending backup to relay: '.$path);

        SendBackupToRelay::dispatchSync($path, [
            'ran_at' => now()->toIso8601String(),
        ]);

        $this->info('Backup sent successfully.');
        return self::SUCCESS;
    }

    protected function latestBackupPath(): ?string
    {
        $dir = storage_path('app/backups');
        if (!is_dir($dir)) {
            return null;
        }

        $files = glob($dir.'/*.sql*') ?: [];
        if (empty($files)) {
            return null;
        }

        usort($files, static fn ($a, $b) => filemtime($b) <=> filemtime($a));
        return Arr::first($files);
    }
}
