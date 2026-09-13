<?php

use App\Services\ApprovedProductPriceService;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('approved_product_prices')) {
            Schema::create('approved_product_prices', function (Blueprint $table) {
                $table->id();
                $table->string('code', 80)->unique();
                $table->string('name');
                $table->text('match_tokens');
                $table->decimal('sale_price_uzs', 18, 2)->nullable();
                $table->decimal('factory_price_uzs', 18, 2)->nullable();
                $table->unsignedInteger('sort_order')->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('approved_product_price_audits')) {
            Schema::create('approved_product_price_audits', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('approved_product_price_id')->index();
                $table->integer('user_id')->nullable()->index();
                $table->string('user_name')->nullable();
                $table->string('price_name');
                $table->decimal('old_sale_price_uzs', 18, 2)->nullable();
                $table->decimal('new_sale_price_uzs', 18, 2)->nullable();
                $table->decimal('old_factory_price_uzs', 18, 2)->nullable();
                $table->decimal('new_factory_price_uzs', 18, 2)->nullable();
                $table->string('ip_address', 45)->nullable();
                $table->text('user_agent')->nullable();
                $table->timestamps();
            });
        }

        if (DB::table('approved_product_prices')->count() === 0) {
            $now = now();
            $rows = collect(ApprovedProductPriceService::defaultRules())
                ->values()
                ->map(fn (array $rule, int $index) => [
                    'code' => $rule['code'],
                    'name' => $rule['name'],
                    'match_tokens' => json_encode($rule['needles'], JSON_UNESCAPED_UNICODE),
                    'sale_price_uzs' => $rule['sale_uzs'],
                    'factory_price_uzs' => $rule['factory_uzs'],
                    'sort_order' => $index + 1,
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ])
                ->all();

            DB::table('approved_product_prices')->insert($rows);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('approved_product_price_audits');
        Schema::dropIfExists('approved_product_prices');
    }
};
