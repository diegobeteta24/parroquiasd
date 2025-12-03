<?php

namespace App\Console\Commands\Concerns;

use Illuminate\Support\Facades\Log;

trait PreventsDestructiveActions
{
    protected function abortIfDestructiveIsDisabled(): bool
    {
        if (config('app.allow_destructive_commands')) {
            return false;
        }

        $commandName = method_exists($this, 'getName') ? $this->getName() : static::class;

        Log::warning('Destructive artisan command blocked', [
            'command' => $commandName,
            'class' => static::class,
            'env' => app()->environment(),
            'user' => get_current_user(),
            'hostname' => php_uname('n'),
        ]);

        $this->error('Este comando está bloqueado para proteger la base de datos.');
        $this->line('Define ALLOW_DESTRUCTIVE_COMMANDS=true en el entorno donde deseas ejecutar y vuelve a intentarlo.');

        return true;
    }
}
