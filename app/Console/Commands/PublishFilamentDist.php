<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class PublishFilamentDist extends Command
{
    protected $signature = 'filament:publish-dist';
    protected $description = 'Copia los archivos dist de los paquetes Filament a public/filament';

    public function handle(Filesystem $fs): int
    {
        $packages = [
            'filament/filament' => ['dist' => ['echo.js','index.js','theme.css']],
            'filament/support' => ['dist' => ['index.css','index.js','async-alpine.js']],
            'filament/forms' => ['dist' => ['index.css','index.js']],
            'filament/notifications' => ['dist' => ['index.js']],
            'filament/tables' => ['dist' => ['index.js']],
        ];
        $publicBase = public_path('filament');
        if (!$fs->exists($publicBase)) { $fs->makeDirectory($publicBase, 0755, true); }
        $copied = 0;
        foreach ($packages as $pkg => $data) {
            [$vendor, $name] = explode('/', $pkg);
            $sourceBase = base_path("vendor/$vendor/$name/dist");
            $targetBase = $publicBase.'/'.$name;
            if (!$fs->exists($sourceBase)) { $this->warn("Skip $pkg (no dist)"); continue; }
            if (!$fs->exists($targetBase)) { $fs->makeDirectory($targetBase,0755,true); }
            foreach ($data['dist'] as $file) {
                $src = $sourceBase.'/'.$file;
                if ($fs->exists($src)) {
                    $fs->copy($src, $targetBase.'/'.$file);
                    $copied++;
                }
            }
        }
        $this->info("Filament dist assets copiados: $copied");
        return self::SUCCESS;
    }
}
