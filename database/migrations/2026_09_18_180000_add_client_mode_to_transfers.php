<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transfers', function (Blueprint $table) {
            $table->string('transfer_type', 20)->default('warehouse')->after('id')->index();
            $table->unsignedBigInteger('client_out_id')->nullable()->after('warehouse_in')->index();
            $table->unsignedBigInteger('client_in_id')->nullable()->after('client_out_id')->index();
        });

        Schema::table('transfer_details', function (Blueprint $table) {
            $table->decimal('unit_price', 20, 4)->nullable()->after('qty');
            $table->decimal('total_price', 20, 4)->nullable()->after('unit_price');
            $table->unsignedBigInteger('currency_type')->nullable()->after('total_price');
            $table->decimal('currency_type_price', 20, 4)->nullable()->after('currency_type');
        });
    }

    public function down(): void
    {
        Schema::table('transfer_details', function (Blueprint $table) {
            $table->dropColumn(['unit_price', 'total_price', 'currency_type', 'currency_type_price']);
        });

        Schema::table('transfers', function (Blueprint $table) {
            $table->dropIndex(['transfer_type']);
            $table->dropIndex(['client_out_id']);
            $table->dropIndex(['client_in_id']);
            $table->dropColumn(['transfer_type', 'client_out_id', 'client_in_id']);
        });
    }
};
