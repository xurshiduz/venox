<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('supplier_return_requests', function (Blueprint $table) {
            $table->id();
            $table->uuid('code')->unique();
            $table->date('date');
            $table->unsignedBigInteger('checkin_id');
            $table->unsignedBigInteger('warehouse_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedTinyInteger('status')->default(0);
            $table->timestamp('accepted_at')->nullable();
            $table->timestamps();
        });
        Schema::create('supplier_return_request_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('supplier_return_request_id');
            $table->unsignedBigInteger('product_id');
            $table->decimal('qty', 20, 4);
            $table->decimal('price', 20, 4)->default(0);
            $table->decimal('total_price', 20, 4)->default(0);
            $table->timestamps();
            $table->foreign('supplier_return_request_id', 'srr_details_header_fk')
                ->references('id')->on('supplier_return_requests')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supplier_return_request_details');
        Schema::dropIfExists('supplier_return_requests');
    }
};
