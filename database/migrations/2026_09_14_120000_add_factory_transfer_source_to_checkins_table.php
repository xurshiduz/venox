<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('checkins', function (Blueprint $table) {
            $table->string('source_system', 32)->nullable()->after('code');
            $table->unsignedBigInteger('source_id')->nullable()->after('source_system');
            $table->uuid('source_code')->nullable()->after('source_id');
            $table->timestamp('source_received_at')->nullable()->after('source_code');
            $table->unique(['source_system', 'source_code'], 'checkins_source_unique');
        });
    }

    public function down(): void
    {
        Schema::table('checkins', function (Blueprint $table) {
            $table->dropUnique('checkins_source_unique');
            $table->dropColumn(['source_system', 'source_id', 'source_code', 'source_received_at']);
        });
    }
};
