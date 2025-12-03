<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        DB::statement("ALTER TABLE `intentions` MODIFY `payment_method` ENUM('cash','transfer','card','recurrente') NOT NULL DEFAULT 'cash'");
        DB::statement("ALTER TABLE `intentions` MODIFY `channel` ENUM('web','counter','online') NOT NULL DEFAULT 'counter'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE `intentions` MODIFY `payment_method` ENUM('cash','transfer','card') NOT NULL DEFAULT 'cash'");
        DB::statement("ALTER TABLE `intentions` MODIFY `channel` ENUM('web','counter') NOT NULL DEFAULT 'counter'");
    }
};
