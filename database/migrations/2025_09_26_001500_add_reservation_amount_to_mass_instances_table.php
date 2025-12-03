<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('mass_instances', function (Blueprint $table) {
            $table->decimal('reservation_amount', 10, 2)->nullable()->after('special_details');
        });
    }

    public function down(): void
    {
        Schema::table('mass_instances', function (Blueprint $table) {
            $table->dropColumn('reservation_amount');
        });
    }
};
