<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // Add a generated column to reference only special masses' starts_at
        try {
            DB::statement("ALTER TABLE mass_instances ADD COLUMN IF NOT EXISTS special_starts_at DATETIME GENERATED ALWAYS AS (CASE WHEN is_special = 1 THEN starts_at ELSE NULL END) STORED");
        } catch (Throwable $e) {
            // Column might already exist (from a previous failed attempt); ignore
        }

        // Try to enforce uniqueness; if duplicates exist, fall back to a non-unique index and let app-level validation prevent new duplicates.
        try {
            DB::statement("CREATE UNIQUE INDEX uniq_special_starts_at ON mass_instances (special_starts_at)");
        } catch (Throwable $e) {
            // Duplicate data prevents unique index; create a regular index instead to speed up checks.
            try { DB::statement("CREATE INDEX idx_special_starts_at ON mass_instances (special_starts_at)"); } catch (Throwable $ignored) {}
        }
    }

    public function down(): void
    {
        try { DB::statement("DROP INDEX uniq_special_starts_at ON mass_instances"); } catch (Throwable $e) {}
        try { DB::statement("DROP INDEX idx_special_starts_at ON mass_instances"); } catch (Throwable $e) {}
        try { DB::statement("ALTER TABLE mass_instances DROP COLUMN IF EXISTS special_starts_at"); } catch (Throwable $e) {}
    }
};
