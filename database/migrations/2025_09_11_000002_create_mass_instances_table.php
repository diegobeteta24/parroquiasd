<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('mass_instances', function (Blueprint $table) {
            $table->id();
            $table->dateTime('starts_at');
            $table->smallInteger('capacity');
            $table->smallInteger('occupied')->default(0);
            $table->enum('status',['scheduled','cancelled','celebrated'])->default('scheduled');
            $table->enum('source',['template','override']);
            $table->timestamps();
            $table->unique(['starts_at']);
            $table->index(['status']);
        });
    }
    public function down(): void { Schema::dropIfExists('mass_instances'); }
};
