<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cash_expenditures', function (Blueprint $table) {
            $table->string('source_system', 32)->nullable()->after('source_cash_receipt_id');
            $table->string('source_type', 32)->nullable()->after('source_system');
            $table->unsignedBigInteger('source_id')->nullable()->after('source_type');
            $table->uuid('source_code')->nullable()->after('source_id');
            $table->unique(['source_system', 'source_type', 'source_id'], 'cash_exp_factory_source_unique');
        });
    }

    public function down(): void
    {
        Schema::table('cash_expenditures', function (Blueprint $table) {
            $table->dropUnique('cash_exp_factory_source_unique');
            $table->dropColumn(['source_system', 'source_type', 'source_id', 'source_code']);
        });
    }
};
