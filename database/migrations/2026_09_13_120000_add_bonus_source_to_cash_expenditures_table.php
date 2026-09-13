<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('cash_expenditures', 'bonus_client_id')) {
            Schema::table('cash_expenditures', function (Blueprint $table) {
                // Legacy VENOX tables use signed INT identifiers.
                $table->integer('bonus_client_id')->nullable()->after('supplier_id')->index();
            });
        }

        if (! Schema::hasColumn('cash_expenditures', 'source_cash_receipt_id')) {
            Schema::table('cash_expenditures', function (Blueprint $table) {
                $table->integer('source_cash_receipt_id')->nullable()->after('bonus_client_id')->index();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('cash_expenditures', 'source_cash_receipt_id')) {
            Schema::table('cash_expenditures', function (Blueprint $table) {
                $table->dropColumn('source_cash_receipt_id');
            });
        }

        if (Schema::hasColumn('cash_expenditures', 'bonus_client_id')) {
            Schema::table('cash_expenditures', function (Blueprint $table) {
                $table->dropColumn('bonus_client_id');
            });
        }
    }
};
