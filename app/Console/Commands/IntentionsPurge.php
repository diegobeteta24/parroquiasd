<?php

namespace App\Console\Commands;

use App\Console\Commands\Concerns\PreventsDestructiveActions;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\Intention;
use App\Models\Image;

class IntentionsPurge extends Command
{
    use PreventsDestructiveActions;

    protected $signature = 'intentions:purge {--dry-run : Solo muestra conteos, no elimina} {--yes : Ejecuta sin pedir confirmación}';
    protected $description = 'Elimina TODAS las intenciones (ordinarias, rosario y especiales), con adjuntos y relaciones; reinicia cupos.';

    public function handle(): int
    {
        if ($this->abortIfDestructiveIsDisabled()) {
            return self::FAILURE;
        }

        $dry = (bool) $this->option('dry-run');

        $counts = [
            'intentions_total' => Intention::withTrashed()->count(),
            'intentions_active' => Intention::count(),
            'dedicatees' => DB::table('intention_dedicatees')->count(),
            'histories' => DB::table('intention_histories')->count(),
            'receipt_images' => Image::where('collection','receipt')->where('imageable_type', Intention::class)->count(),
        ];

        $this->info('Conteo actual:');
        foreach ($counts as $k => $v) { $this->line("- {$k}: {$v}"); }

        if ($dry) {
            $this->comment('Dry-run: no se eliminó nada. Ejecuta sin --dry-run para purgar.');
            return self::SUCCESS;
        }

        if (!$this->option('yes')) {
            if (!$this->confirm('Esto eliminará TODAS las intenciones y archivos de recibo. ¿Deseas continuar?')) {
                $this->warn('Cancelado.');
                return self::SUCCESS;
            }
        }

        DB::transaction(function () {
            // 1) Limpiar historiales y dedicatarios (si no hay cascada)
            try { DB::table('intention_histories')->delete(); } catch (\Throwable $e) {}
            try { DB::table('intention_dedicatees')->delete(); } catch (\Throwable $e) {}

            // 2) Eliminar archivos de recibo + registros de imagen
            $images = Image::where('collection','receipt')
                ->where('imageable_type', Intention::class)
                ->get(['id','disk','path']);
            foreach ($images as $img) {
                try { Storage::disk($img->disk ?: 'public')->delete($img->path); } catch (\Throwable $e) {}
                $img->delete();
            }

            // 3) Eliminar intenciones definitivamente (incluye soft-deleted)
            Intention::query()->forceDelete();

            // 4) Reiniciar ocupados de todas las misas
            DB::table('mass_instances')->update(['occupied' => 0]);
        });

        $this->info('Listo: se purgaron las intenciones y se reiniciaron los cupos.');
        return self::SUCCESS;
    }
}
