<?php

namespace App\Services;

class ApprovedProductPriceService
{
    /**
     * BOSS RUXSAT BERGAN NARX.xlsx, 13.08.2026.
     * Prices are per canister/unit in UZS. More specific rules must come first.
     */
    private const RULES = [
        [['0w20', '4l'], 207000, 192000],

        [['5w30', 'molygreen', '4l'], 207000, 192000],
        [['5w30', 'premium', '1l'], 52000, 48000],
        [['5w30', 'premium', '3l'], 156000, 144000],
        [['5w30', '4l'], 207500, 192000],

        [['10w40', 'molygreen', '4l'], 197000, 192000],
        [['10w40', 'sn', '208l'], null, 9360000],
        [['10w40', 'sl', '208l'], null, 7904000],
        [['10w40', 'sf', '208l'], null, 7176000],
        [['10w40', 'sn', '1l'], 49000, 46000],
        [['10w40', 'sn', '3l'], 148000, 138000],
        [['10w40', 'sn', '4l'], 197000, 184000],
        [['10w40', 'sl', '1l'], 44000, 41000],
        [['10w40', 'sl', '3l'], 132000, 123000],
        [['10w40', 'sl', '4l'], 176000, 164000],

        [['atf', 'cvt', '5l'], 290000, 261000],
        [['atfiii', '1l'], 49000, 52200],
        [['dexron', '1l'], 57000, 52200],
        [['dexron', '5l'], 283000, 261000],
        [['80w90', '1l'], 49000, 52200],
        [['85w140', '1l'], 49000, 52200],

        [['antif', '40', '5l'], 84000, 78000],
        [['antif', '40', '1l'], 16800, 15600],
        [['antif', '35', '5l'], null, 72000],
        [['antif', '35', '1l'], null, 14400],

        [['15w40', '20l'], null, 800000],
        [['20w50', '20l'], null, 880000],
        [['80w90', '20l'], null, 740000],
        [['85w140', '20l'], null, 740000],
        [['46lhm', '20l'], null, 600000],
    ];

    public function pricesFor(string $productName): ?array
    {
        $normalized = $this->normalize($productName);

        foreach (self::RULES as [$needles, $salePriceUzs, $factoryPriceUzs]) {
            $matches = true;
            foreach ($needles as $needle) {
                if (strpos($normalized, $needle) === false) {
                    $matches = false;
                    break;
                }
            }

            if ($matches) {
                return [
                    'sale_uzs' => $salePriceUzs,
                    'factory_uzs' => $factoryPriceUzs,
                ];
            }
        }

        return null;
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
