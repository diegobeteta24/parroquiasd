<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('mass_overrides', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->enum('action',['add','remove','close_day']);
            $table->time('time')->nullable();
            $table->smallInteger('capacity')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();
            $table->index(['date','action']);
        });
    }
    public function down(): void { Schema::dropIfExists('mass_overrides'); }
};
