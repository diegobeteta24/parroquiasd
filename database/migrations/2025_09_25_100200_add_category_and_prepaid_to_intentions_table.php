<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('intentions', function (Blueprint $table) {
            $table->enum('category', ['acciones_de_gracia','peticiones','difuntos'])->nullable()->after('type');
            $table->boolean('is_prepaid')->default(false)->after('channel');
            $table->decimal('stipend_amount_gtq',10,2)->nullable()->after('amount');
            $table->string('payment_ref')->nullable()->after('payment_method');
            $table->timestamp('paid_at')->nullable()->after('status');
            $table->index(['category']);
            $table->index(['is_prepaid']);
            $table->index(['paid_at']);
        });
    }

    public function down(): void
    {
        Schema::table('intentions', function (Blueprint $table) {
            $table->dropIndex(['category']);
            $table->dropIndex(['is_prepaid']);
            $table->dropIndex(['paid_at']);
            $table->dropColumn(['category','is_prepaid','stipend_amount_gtq','payment_ref','paid_at']);
        });
    }
};
