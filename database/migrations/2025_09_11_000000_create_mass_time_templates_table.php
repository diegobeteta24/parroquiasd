<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('mass_time_templates', function (Blueprint $table) {
            $table->id();
            $table->tinyInteger('dow');
            $table->time('time');
            $table->smallInteger('capacity')->default(10);
            $table->boolean('active')->default(true);
            $table->timestamps();
            $table->unique(['dow','time']);
        });
    }
    public function down(): void { Schema::dropIfExists('mass_time_templates'); }
};
