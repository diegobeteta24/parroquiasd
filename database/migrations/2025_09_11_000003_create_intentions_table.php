<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('intentions', function (Blueprint $table) {
            $table->id();
            
            // Recurrente Payment Intent ID (idempotencia de webhooks)
            $table->string('payment_intent_id')->nullable()->unique();
            
            $table->foreignId('mass_instance_id')->constrained()->cascadeOnDelete();
            $table->string('type',50);
            $table->text('public_text')->nullable();
            $table->string('donor_name');
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            
            // Montos: amount (legacy decimal) + amount_in_cents (preciso)
            $table->decimal('amount',10,2)->nullable();
            $table->unsignedInteger('amount_in_cents')->nullable();
            $table->string('currency', 3)->default('GTQ');
            
            // Metadata JSON del webhook
            $table->json('metadata')->nullable();
            
            $table->enum('status',['held','confirmed','paid','celebrated','cancelled'])->default('confirmed');
            $table->enum('channel',['web','counter'])->default('counter');
            $table->timestamp('hold_expires_at')->nullable();
            $table->string('code',16)->unique();
            $table->timestamps();
            $table->index(['mass_instance_id','status']);
            $table->index('payment_intent_id');
        });
    }
    public function down(): void { Schema::dropIfExists('intentions'); }
};
