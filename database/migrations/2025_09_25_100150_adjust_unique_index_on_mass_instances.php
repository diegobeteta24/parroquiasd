<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Drop legacy unique on starts_at to allow parallel special masses
        $exists = collect(DB::select("SHOW INDEX FROM mass_instances WHERE Key_name = 'mass_instances_starts_at_unique'"))->isNotEmpty();
        if ($exists) {
            DB::statement('ALTER TABLE mass_instances DROP INDEX mass_instances_starts_at_unique');
        }
    }

    public function down(): void
    {
        Schema::table('mass_instances', function (Blueprint $table) {
            $table->unique(['starts_at']);
        });
    }
};
