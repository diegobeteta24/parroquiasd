<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('intentions', function (Blueprint $table) {
            $table->uuid('group_code')->nullable()->after('code');
            $table->index('group_code');
        });
    }

    public function down(): void
    {
        Schema::table('intentions', function (Blueprint $table) {
            $table->dropIndex(['group_code']);
            $table->dropColumn('group_code');
        });
    }
};
