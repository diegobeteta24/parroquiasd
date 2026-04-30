<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BackupDatabase extends Command
{
    protected $signature = 'backup:database {--keep=7 : Number of days to keep backups}';
    protected $description = 'Dump the database into storage/app/backups as a timestamped file';

    public function handle(): int
    {
        $connection = config('database.default');
        $config = config("database.connections.$connection");
        $dir = storage_path('app/backups');
        if (!is_dir($dir)) mkdir($dir, 0775, true);

        $ts = now()->format('Y-m-d_His');
        $filename = $dir . DIRECTORY_SEPARATOR . ($connection . '_' . $ts . '.sql');

        try {
            switch ($config['driver']) {
                case 'mysql':
                    $host = $config['host'] ?? '127.0.0.1';
                    $port = $config['port'] ?? 3306;
                    $database = $config['database'];
                    $username = $config['username'];
                    $password = $config['password'] ?? '';
                    $extra = $config['sslmode'] ?? '';
                    $env = '';
                    if ($password) { $env = 'MYSQL_PWD=' . escapeshellarg($password) . ' '; }
                    $cmd = sprintf(
                        "%smysqldump --host=%s --port=%d --user=%s --routines --events --single-transaction --no-tablespaces %s > %s",
                        $env,
                        escapeshellarg($host),
                        $port,
                        escapeshellarg($username),
                        escapeshellarg($database),
                        escapeshellarg($filename)
                    );
                    break;
                case 'pgsql':
                    $host = $config['host'] ?? '127.0.0.1';
                    $port = $config['port'] ?? 5432;
                    $database = $config['database'];
                    $username = $config['username'];
                    $password = $config['password'] ?? '';
                    $env = '';
                    if ($password) { $env = 'PGPASSWORD=' . escapeshellarg($password) . ' '; }
                    $cmd = sprintf(
                        "%spg_dump --host=%s --port=%d --username=%s --format=plain --no-owner --no-privileges %s > %s",
                        $env,
                        escapeshellarg($host),
                        $port,
                        escapeshellarg($username),
                        escapeshellarg($database),
                        escapeshellarg($filename)
                    );
                    break;
                case 'sqlite':
                    $path = $config['database'];
                    $cmd = sprintf('sqlite3 %s ".backup %s"', escapeshellarg($path), escapeshellarg($filename.'.sqlite3'));
                    break;
                default:
                    $this->error('Unsupported driver for backup: ' . $config['driver']);
                    return self::FAILURE;
            }

            $this->info('Running: ' . preg_replace('/\s+/', ' ', $cmd));
            $result = 0;
            system($cmd, $result);
            if ($result !== 0) {
                $this->error('Backup failed with code ' . $result);
                return self::FAILURE;
            }

            // Cleanup old backups
            $keepDays = (int) $this->option('keep');
            $files = glob($dir . DIRECTORY_SEPARATOR . ($connection . '_*.sql*')) ?: [];
            $threshold = now()->subDays($keepDays);
            foreach ($files as $file) {
                $base = basename($file);
                // pattern: connection_YYYY-mm-dd_HHMMSS.sql
                if (preg_match('/^[^_]+_(\d{4}-\d{2}-\d{2})_(\d{6})\.sql(\.sqlite3)?$/', $base, $m)) {
                    $dt = \Carbon\Carbon::createFromFormat('Y-m-d_His', $m[1] . '_' . $m[2]);
                    if ($dt && $dt->lt($threshold)) {
                        @unlink($file);
                    }
                }
            }

            $this->info('Backup saved to: ' . $filename);
            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error($e->getMessage());
            return self::FAILURE;
        }
    }
}
