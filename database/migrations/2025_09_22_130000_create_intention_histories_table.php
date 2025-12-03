<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('intention_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('intention_id')->constrained('intentions')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action'); // updated|deleted
            $table->text('justification');
            $table->json('changes')->nullable(); // before/after snapshot if needed
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('intention_histories');
    }
};
