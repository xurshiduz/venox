<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('checkouts', 'vehicle_number')) {
            Schema::table('checkouts', function (Blueprint $table) {
                $table->string('vehicle_number', 32)->nullable()->after('reference');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('checkouts', 'vehicle_number')) {
            Schema::table('checkouts', function (Blueprint $table) {
                $table->dropColumn('vehicle_number');
            });
        }
    }
};
