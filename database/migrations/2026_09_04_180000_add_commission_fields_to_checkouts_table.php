<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('checkouts', function (Blueprint $table) {
            if (! Schema::hasColumn('checkouts', 'commission_scheme')) {
                $table->string('commission_scheme', 32)->nullable()->after('discount');
            }
            if (! Schema::hasColumn('checkouts', 'kpi_percent')) {
                $table->decimal('kpi_percent', 5, 2)->default(0)->after('commission_scheme');
            }
            if (! Schema::hasColumn('checkouts', 'agent_percent')) {
                $table->decimal('agent_percent', 5, 2)->default(0)->after('kpi_percent');
            }
            if (! Schema::hasColumn('checkouts', 'venox_bonus_percent')) {
                $table->decimal('venox_bonus_percent', 5, 2)->default(0)->after('agent_percent');
            }
        });
    }

    public function down(): void
    {
        Schema::table('checkouts', function (Blueprint $table) {
            $columns = array_values(array_filter([
                'commission_scheme',
                'kpi_percent',
                'agent_percent',
                'venox_bonus_percent',
            ], fn ($column) => Schema::hasColumn('checkouts', $column)));

            if ($columns) {
                $table->dropColumn($columns);
            }
        });
    }
};
