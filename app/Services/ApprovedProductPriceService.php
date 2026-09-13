<?php

namespace App\Services;

use App\Models\ApprovedProductPrice;
use Illuminate\Support\Facades\Schema;

class ApprovedProductPriceService
{
    /** The USD rate used by the approved 13.08.2026 price sheets/sample. */
    private const USD_RATE = 11900;

    private ?array $resolvedRules = null;

    public function pricesFor(string $productName): ?array
    {
        $normalized = $this->normalize($productName);

        foreach ($this->rules() as $rule) {
            $matches = true;
            foreach ($rule['needles'] as $needle) {
                if (strpos($normalized, $needle) === false) {
                    $matches = false;
                    break;
                }
            }

            if ($matches) {
                return [
                    'sale_uzs' => $rule['sale_uzs'],
                    'factory_uzs' => $rule['factory_uzs'],
                ];
            }
        }

        return null;
    }

    public function usdRate(): float
    {
        return self::USD_RATE;
    }

    /**
     * BOSS RUXSAT BERGAN NARX.xlsx, 13.08.2026.
     * Prices are per canister/unit in UZS. More specific rules must come first.
     */
    public static function defaultRules(): array
    {
        return [
            ['code' => '0w20-4l', 'name' => 'Venox 0W-20 4L', 'needles' => ['0w20', '4l'], 'sale_uzs' => 207000, 'factory_uzs' => 192000],
            ['code' => '5w30-molygreen-4l', 'name' => 'Venox 5W-30 Molygreen 4L', 'needles' => ['5w30', 'molygreen', '4l'], 'sale_uzs' => 207000, 'factory_uzs' => 192000],
            ['code' => '5w30-premium-1l', 'name' => 'Venox 5W-30 Premium 1L', 'needles' => ['5w30', 'premium', '1l'], 'sale_uzs' => 52000, 'factory_uzs' => 48000],
            ['code' => '5w30-premium-3l', 'name' => 'Venox 5W-30 Premium 3L', 'needles' => ['5w30', 'premium', '3l'], 'sale_uzs' => 156000, 'factory_uzs' => 144000],
            ['code' => '5w30-4l', 'name' => 'Venox 5W-30 4L', 'needles' => ['5w30', '4l'], 'sale_uzs' => 207500, 'factory_uzs' => 192000],
            ['code' => '10w40-molygreen-4l', 'name' => 'Venox 10W-40 Molygreen 4L', 'needles' => ['10w40', 'molygreen', '4l'], 'sale_uzs' => 197000, 'factory_uzs' => 192000],
            ['code' => '10w40-sn-208l', 'name' => 'Venox 10W-40 SN 208L', 'needles' => ['10w40', 'sn', '208l'], 'sale_uzs' => null, 'factory_uzs' => 9360000],
            ['code' => '10w40-sl-208l', 'name' => 'Venox 10W-40 SL 208L', 'needles' => ['10w40', 'sl', '208l'], 'sale_uzs' => null, 'factory_uzs' => 7904000],
            ['code' => '10w40-sf-208l', 'name' => 'Venox 10W-40 SF 208L', 'needles' => ['10w40', 'sf', '208l'], 'sale_uzs' => null, 'factory_uzs' => 7176000],
            ['code' => '10w40-sn-1l', 'name' => 'Venox 10W-40 SN 1L', 'needles' => ['10w40', 'sn', '1l'], 'sale_uzs' => 49000, 'factory_uzs' => 46000],
            ['code' => '10w40-sn-3l', 'name' => 'Venox 10W-40 SN 3L', 'needles' => ['10w40', 'sn', '3l'], 'sale_uzs' => 148000, 'factory_uzs' => 138000],
            ['code' => '10w40-sn-4l', 'name' => 'Venox 10W-40 SN 4L', 'needles' => ['10w40', 'sn', '4l'], 'sale_uzs' => 197000, 'factory_uzs' => 184000],
            ['code' => '10w40-sl-1l', 'name' => 'Venox 10W-40 SL 1L', 'needles' => ['10w40', 'sl', '1l'], 'sale_uzs' => 44000, 'factory_uzs' => 41000],
            ['code' => '10w40-sl-3l', 'name' => 'Venox 10W-40 SL 3L', 'needles' => ['10w40', 'sl', '3l'], 'sale_uzs' => 132000, 'factory_uzs' => 123000],
            ['code' => '10w40-sl-4l', 'name' => 'Venox 10W-40 SL 4L', 'needles' => ['10w40', 'sl', '4l'], 'sale_uzs' => 176000, 'factory_uzs' => 164000],
            ['code' => 'atf-cvt-5l', 'name' => 'Venox ATF CVT 5L', 'needles' => ['atf', 'cvt', '5l'], 'sale_uzs' => 290000, 'factory_uzs' => 261000],
            ['code' => 'atf-iii-1l', 'name' => 'Venox ATF-III 1L', 'needles' => ['atfiii', '1l'], 'sale_uzs' => 49000, 'factory_uzs' => 52200],
            ['code' => 'dexron-1l', 'name' => 'Venox Dexron 1L', 'needles' => ['dexron', '1l'], 'sale_uzs' => 57000, 'factory_uzs' => 52200],
            ['code' => 'dexron-5l', 'name' => 'Venox Dexron 5L', 'needles' => ['dexron', '5l'], 'sale_uzs' => 283000, 'factory_uzs' => 261000],
            ['code' => '80w90-1l', 'name' => 'Venox 80W-90 1L', 'needles' => ['80w90', '1l'], 'sale_uzs' => 49000, 'factory_uzs' => 52200],
            ['code' => '85w140-1l', 'name' => 'Venox 85W-140 1L', 'needles' => ['85w140', '1l'], 'sale_uzs' => 49000, 'factory_uzs' => 52200],
            ['code' => 'antifreeze-40-5l', 'name' => 'Venox Antifreeze -40°C 5L', 'needles' => ['antif', '40', '5l'], 'sale_uzs' => 84000, 'factory_uzs' => 78000],
            ['code' => 'antifreeze-40-1l', 'name' => 'Venox Antifreeze -40°C 1L', 'needles' => ['antif', '40', '1l'], 'sale_uzs' => 16800, 'factory_uzs' => 15600],
            ['code' => 'antifreeze-35-5l', 'name' => 'Venox Antifreeze -35°C 5L', 'needles' => ['antif', '35', '5l'], 'sale_uzs' => null, 'factory_uzs' => 72000],
            ['code' => 'antifreeze-35-1l', 'name' => 'Venox Antifreeze -35°C 1L', 'needles' => ['antif', '35', '1l'], 'sale_uzs' => null, 'factory_uzs' => 14400],
            ['code' => '15w40-20l', 'name' => 'Venox 15W-40 20L', 'needles' => ['15w40', '20l'], 'sale_uzs' => null, 'factory_uzs' => 800000],
            ['code' => '20w50-20l', 'name' => 'Venox 20W-50 20L', 'needles' => ['20w50', '20l'], 'sale_uzs' => null, 'factory_uzs' => 880000],
            ['code' => '80w90-20l', 'name' => 'Venox 80W-90 20L', 'needles' => ['80w90', '20l'], 'sale_uzs' => null, 'factory_uzs' => 740000],
            ['code' => '85w140-20l', 'name' => 'Venox 85W-140 20L', 'needles' => ['85w140', '20l'], 'sale_uzs' => null, 'factory_uzs' => 740000],
            ['code' => '46lhm-20l', 'name' => 'Venox 46LHM 20L', 'needles' => ['46lhm', '20l'], 'sale_uzs' => null, 'factory_uzs' => 600000],
        ];
    }

    private function rules(): array
    {
        if ($this->resolvedRules !== null) {
            return $this->resolvedRules;
        }

        try {
            if (Schema::hasTable('approved_product_prices')) {
                $databaseRules = ApprovedProductPrice::query()
                    ->where('is_active', true)
                    ->orderBy('sort_order')
                    ->orderBy('id')
                    ->get()
                    ->map(fn (ApprovedProductPrice $price) => [
                        'needles' => $price->match_tokens ?: [],
                        'sale_uzs' => $price->sale_price_uzs !== null ? (float) $price->sale_price_uzs : null,
                        'factory_uzs' => $price->factory_price_uzs !== null ? (float) $price->factory_price_uzs : null,
                    ])
                    ->filter(fn (array $rule) => ! empty($rule['needles']))
                    ->values()
                    ->all();

                if ($databaseRules) {
                    return $this->resolvedRules = $databaseRules;
                }
            }
        } catch (\Throwable $exception) {
            // Deploy vaqtida migration hali bajarilmagan bo'lsa, hisobot ishlashda
            // davom etishi uchun koddagi boshlang'ich narxlarga qaytamiz.
        }

        return $this->resolvedRules = static::defaultRules();
    }

    private function normalize(string $name): string
    {
        $name = mb_strtolower($name, 'UTF-8');
        $name = strtr($name, [
            'ё' => 'е',
            '|' => 'i',
            'л' => 'l',
        ]);

        return preg_replace('/[^a-z0-9а-я]+/u', '', $name) ?: '';
    }
}
