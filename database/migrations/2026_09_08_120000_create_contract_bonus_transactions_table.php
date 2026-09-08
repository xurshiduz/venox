<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('contract_bonus_transactions')) {
            return;
        }

        Schema::create('contract_bonus_transactions', function (Blueprint $table) {
            $table->id();
            $table->uuid('code')->unique();
            // Legacy VENОX tables use signed INT identifiers, so these columns
            // deliberately match them instead of using Laravel's BIGINT default.
            $table->integer('client_id')->index();
            $table->integer('cash_receipt_id')->nullable()->index();
            $table->integer('checkout_id')->nullable()->index();
            $table->integer('user_id')->nullable()->index();
            $table->date('transaction_date')->index();
            $table->enum('type', ['accrual', 'gift', 'cash', 'debt_offset']);
            $table->enum('direction', ['credit', 'debit']);
            $table->decimal('amount_usd', 18, 4);
            $table->string('note', 500)->nullable();
            $table->json('meta')->nullable();
            $table->boolean('status')->default(true)->index();
            $table->timestamps();

            $table->unique(['cash_receipt_id', 'type'], 'contract_bonus_source_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contract_bonus_transactions');
    }
};
