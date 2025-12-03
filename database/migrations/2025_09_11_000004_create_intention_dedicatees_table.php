<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('intention_dedicatees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('intention_id')->constrained('intentions')->cascadeOnDelete();
            $table->string('name');
            $table->string('relation')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('intention_dedicatees'); }
};
