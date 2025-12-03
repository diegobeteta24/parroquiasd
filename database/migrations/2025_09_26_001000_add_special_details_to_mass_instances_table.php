<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('mass_instances', function (Blueprint $table) {
            $table->json('special_details')->nullable()->after('notes');
        });
    }

    public function down(): void
    {
        Schema::table('mass_instances', function (Blueprint $table) {
            $table->dropColumn('special_details');
        });
    }
};
