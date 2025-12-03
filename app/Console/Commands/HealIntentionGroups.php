<?php

namespace App\Console\Commands;

use App\Models\Intention;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class HealIntentionGroups extends Command
{
    protected $signature = 'intentions:heal-groups {--apply : Escribir cambios en la base de datos} {--limit=0 : Limitar número de grupos procesados (0 = sin límite)}';
    protected $description = 'Agrupa intenciones repetidas asignándoles un group_code común basado en un heurístico (tipo, donante, texto público).';

    public function handle(): int
    {
        $apply = (bool) $this->option('apply');
        $limit = (int) $this->option('limit');

        $this->info(($apply ? 'APLICANDO' : 'SIMULANDO').': agrupación de intenciones repetidas…');

        // Buscar solo intenciones sin group_code
        $query = Intention::query()->whereNull('group_code')->whereNull('deleted_at');
        $total = $query->count();
        if ($total === 0) {
            $this->info('No hay intenciones sin group_code. Nada que hacer.');
            return self::SUCCESS;
        }

        $processedGroups = 0;
        $grouped = 0; $touched = 0;

        // Haremos un agrupado por firma en memoria por lotes para no cargar todo
        $query->orderBy('id')->chunk(1000, function ($chunk) use ($apply, $limit, &$processedGroups, &$grouped, &$touched) {
            // Construir buckets por firma
            $buckets = [];
            foreach ($chunk as $it) {
                $sig = self::signature($it);
                $buckets[$sig][] = $it;
            }
            foreach ($buckets as $sig => $items) {
                if (count($items) < 2) continue; // no es repetición
                if ($limit && $processedGroups >= $limit) break;
                $processedGroups++;
                $uuid = (string) Str::uuid();
                $this->line(sprintf('Grupo #%d (%s): %d intenciones', $processedGroups, $sig, count($items)));
                foreach ($items as $it) {
                    $grouped++;
                    if ($apply) {
                        $it->group_code = $uuid;
                        $it->save();
                        $touched++;
                    }
                }
            }
        });

        $this->info(sprintf('Firmas procesadas: %d | Intenciones detectadas en grupos: %d | %s: %d', $processedGroups, $grouped, $apply ? 'Actualizadas' : 'Actualizables', $touched));

        if (!$apply) {
            $this->warn('Modo simulación. Ejecute con --apply para escribir los cambios.');
        }

        return self::SUCCESS;
    }

    public static function signature(Intention $i): string
    {
        $norm = fn($v) => trim(mb_strtolower((string) $v));
        $parts = [
            'type:'.$norm($i->type),
            'donor:'.$norm($i->donor_name),
            'text:'.$norm($i->public_text),
            'channel:'.$norm($i->channel),
        ];
        return md5(implode('|', $parts));
    }
}
