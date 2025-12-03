<?php

namespace App\Console\Commands;

use App\Console\Commands\Concerns\PreventsDestructiveActions;
use App\Models\Image;
use App\Models\Intention;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class CleanForClientDemo extends Command
{
    use PreventsDestructiveActions;

    protected $signature = 'app:clean-demo {--force : Run in production or without prompt confirmation}';
    protected $description = 'Clean database for client demo: removes intentions and related data, keeps users/roles/masses/templates';

    public function handle(): int
    {
        if ($this->abortIfDestructiveIsDisabled()) {
            return self::FAILURE;
        }

        if (app()->environment('production') && ! $this->option('force')) {
            $this->error('Refusing to run in production without --force');
            return self::FAILURE;
        }

        if (! $this->option('force') && ! $this->confirm('This will delete intentions, dedicatees, histories, and receipt images. Continue?')) {
            $this->info('Aborted.');
            return self::SUCCESS;
        }

        $connection = config('database.default');
        $driver = config("database.connections.$connection.driver");

        $this->info('Disabling foreign key checks (if supported)...');
        $this->disableForeignKeys($driver);

        try {
            DB::beginTransaction();

            // 1) Delete receipt images tied to intentions (delete physical files too)
            $this->info('Removing receipt images linked to intentions...');
            $images = Image::query()
                ->where(function ($q) {
                    $q->where('imageable_type', Intention::class)
                      ->orWhere('collection', 'receipt');
                })
                ->get();

            $deletedFiles = 0;
            foreach ($images as $img) {
                try {
                    if ($img->disk && $img->path) {
                        Storage::disk($img->disk)->delete($img->path);
                        $deletedFiles++;
                    }
                } catch (\Throwable $e) {
                    // ignore missing files
                }
                $img->delete();
            }
            $this->line(" - Deleted ".$images->count()." image rows, removed $deletedFiles files");

            // 2) Clear dedicatees and histories first due to FKs
            $this->info('Deleting intention dedicatees...');
            DB::table('intention_dedicatees')->delete();

            $this->info('Deleting intention histories...');
            if (Schema::hasTable('intention_histories')) {
                DB::table('intention_histories')->delete();
            }

            // 3) Delete intentions (force)
            $this->info('Deleting intentions (force)...');
            // Use chunking to avoid memory issues
            Intention::withTrashed()->chunkById(1000, function ($chunk) {
                foreach ($chunk as $intent) {
                    $intent->forceDelete();
                }
            });

            // 4) Recalculate occupied counts for masses -> zero them
            $this->info('Resetting occupied counts on mass instances...');
            DB::table('mass_instances')->update(['occupied' => 0]);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->enableForeignKeys($driver);
            $this->error($e->getMessage());
            return self::FAILURE;
        }

        $this->enableForeignKeys($driver);
        $this->info('Done. Kept users, roles/permissions, mass templates and instances.');
        return self::SUCCESS;
    }

    protected function disableForeignKeys(string $driver): void
    {
        try {
            if (in_array($driver, ['mysql','mariadb'])) {
                DB::statement('SET FOREIGN_KEY_CHECKS=0');
            } elseif ($driver === 'pgsql') {
                DB::statement('SET session_replication_role = replica');
            }
        } catch (\Throwable $e) {
            // ignore
        }
    }

    protected function enableForeignKeys(string $driver): void
    {
        try {
            if (in_array($driver, ['mysql','mariadb'])) {
                DB::statement('SET FOREIGN_KEY_CHECKS=1');
            } elseif ($driver === 'pgsql') {
                DB::statement('SET session_replication_role = DEFAULT');
            }
        } catch (\Throwable $e) {
            // ignore
        }
    }
}
