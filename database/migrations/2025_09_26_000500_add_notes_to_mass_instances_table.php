<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('mass_instances', function (Blueprint $table) {
            $table->text('notes')->nullable()->after('special_category');
        });
    }

    public function down(): void
    {
        Schema::table('mass_instances', function (Blueprint $table) {
            $table->dropColumn('notes');
        });
    }
};
