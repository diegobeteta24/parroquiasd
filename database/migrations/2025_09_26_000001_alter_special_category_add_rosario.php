<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'mysql') {
            return; // Only applicable to MySQL/MariaDB enums
        }
        // Ensure the enum includes 'rosario'. This is safe to run even if it already exists.
        // Note: This assumes the original set was ['bautismo','confirmacion','primera_comunion','matrimonio','otra'].
        DB::statement("ALTER TABLE mass_instances MODIFY COLUMN special_category ENUM('bautismo','confirmacion','primera_comunion','matrimonio','rosario','otra') NULL");
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'mysql') {
            return; // Only applicable to MySQL/MariaDB enums
        }
        // Before removing 'rosario' from the enum, map any existing values to 'otra' to avoid errors.
        DB::statement("UPDATE mass_instances SET special_category = 'otra' WHERE special_category = 'rosario'");
        DB::statement("ALTER TABLE mass_instances MODIFY COLUMN special_category ENUM('bautismo','confirmacion','primera_comunion','matrimonio','otra') NULL");
    }
};
