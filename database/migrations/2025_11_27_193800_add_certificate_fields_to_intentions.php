<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('intentions', function (Blueprint $table) {
            $table->string('certificate_token', 80)->nullable()->after('code');
            $table->string('certificate_path')->nullable()->after('certificate_token');
            $table->timestamp('certificate_generated_at')->nullable()->after('certificate_path');
            $table->index('certificate_token');
        });
    }

    public function down(): void
    {
        Schema::table('intentions', function (Blueprint $table) {
            $table->dropIndex(['certificate_token']);
            $table->dropColumn(['certificate_generated_at', 'certificate_path', 'certificate_token']);
        });
    }
};
