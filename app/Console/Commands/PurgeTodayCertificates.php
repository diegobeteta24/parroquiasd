<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PurgeTodayCertificates extends Command
{
    protected $signature = 'certificates:purge {--date=} {--dry-run : Solo muestra qué archivos se eliminarían}';

    protected $description = 'Elimina los certificados PDF generados en la fecha indicada (por defecto hoy).';

    public function handle(): int
    {
        $timezone = config('app.timezone', 'UTC');
        $dateOption = $this->option('date');

        try {
            $targetDate = $dateOption
                ? Carbon::parse($dateOption, $timezone)->startOfDay()
                : now($timezone)->startOfDay();
        } catch (\Throwable $e) {
            $this->error('Fecha inválida. Usa un formato como 2025-12-02.');
            return self::FAILURE;
        }

        $disk = Storage::disk('public');
        $directory = 'certificates/intentions';

        if (!$disk->exists($directory)) {
            $this->info('No existe el directorio de certificados, nada que limpiar.');
            return self::SUCCESS;
        }

        $files = $disk->files($directory);
        $deleted = [];

        foreach ($files as $file) {
            $basename = basename($file);

            if (!Str::endsWith($basename, '.pdf')) {
                continue;
            }

            if (!preg_match('/^(\d+)-(\d{8})(\d{6})\.pdf$/', $basename, $matches)) {
                continue;
            }

            $fileDate = Carbon::createFromFormat('Ymd', $matches[2], $timezone)->startOfDay();

            if ($fileDate->equalTo($targetDate)) {
                if ($this->option('dry-run')) {
                    $deleted[] = $file;
                    continue;
                }

                if ($disk->delete($file)) {
                    $deleted[] = $file;
                }
            }
        }

        if (empty($deleted)) {
            $this->info(sprintf('No se encontraron certificados para la fecha %s.', $targetDate->toDateString()));
            return self::SUCCESS;
        }

        if ($this->option('dry-run')) {
            $this->warn('DRY RUN: Se habrían eliminado los siguientes archivos:');
        } else {
            $this->info('Se eliminaron los siguientes certificados:');
        }

        foreach ($deleted as $file) {
            $this->line(" - {$file}");
        }

        if (!$this->option('dry-run')) {
            $this->newLine();
            $this->info(sprintf('Total eliminados: %d', count($deleted)));
        }

        $this->comment('Nota: los registros de intenciones permanecen intactos en la base de datos.');

        return self::SUCCESS;
    }
}
