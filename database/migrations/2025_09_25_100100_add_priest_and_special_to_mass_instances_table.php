<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('mass_instances', function (Blueprint $table) {
            $table->foreignId('priest_id')->nullable()->after('source')->constrained('priests')->nullOnDelete();
            $table->boolean('is_special')->default(false)->after('priest_id');
            $table->enum('special_category', ['bautismo','confirmacion','primera_comunion','matrimonio','rosario','otra'])->nullable()->after('is_special');
            $table->unique(['starts_at','is_special','special_category']);
            $table->index(['is_special','special_category']);
        });
    }

    public function down(): void
    {
        Schema::table('mass_instances', function (Blueprint $table) {
            $table->dropIndex(['is_special','special_category']);
            $table->dropUnique(['starts_at','is_special','special_category']);
            $table->dropConstrainedForeignId('priest_id');
            $table->dropColumn(['is_special','special_category']);
        });
    }
};
