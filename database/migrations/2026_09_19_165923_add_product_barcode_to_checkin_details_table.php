<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddProductBarcodeToCheckinDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('checkin_details', function (Blueprint $table) {
            $table->string('product_barcode')->nullable()->after('product_id')->index();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('checkin_details', function (Blueprint $table) {
            $table->dropColumn('product_barcode');
        });
    }
}
