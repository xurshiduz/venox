<?php

namespace Tests\Unit;

use App\Exports\CheckoutMonthExport;
use App\Models\Checkout;
use App\Services\ApprovedProductPriceService;
use PHPUnit\Framework\TestCase;

class CheckoutMonthApprovedPriceTest extends TestCase
{
    public function test_approved_uzs_sale_price_is_converted_with_the_document_rate(): void
    {
        $price = CheckoutMonthExport::catalogUnitPriceUsd(207000, 2, 16.50, 11900, '2026-09-11');

        $this->assertEqualsWithDelta(17.39495798, $price, 0.00000001);
    }

    public function test_approved_usd_factory_price_is_kept_unchanged(): void
    {
        $price = CheckoutMonthExport::catalogUnitPriceUsd(16.13445378, 1, 15.50, 11900, '2026-09-11');

        $this->assertEqualsWithDelta(16.13445378, $price, 0.00000001);
    }

    public function test_legacy_product_without_catalogue_price_uses_calculated_fallback(): void
    {
        $price = CheckoutMonthExport::catalogUnitPriceUsd(0, 1, 15.46218487, 11900, '2026-09-11');

        $this->assertSame(15.46218487, $price);
    }

    public function test_legacy_usd_catalogue_price_is_not_divided_by_uzs_rate(): void
    {
        $price = CheckoutMonthExport::catalogUnitPriceUsd(24.50, 2, 20, 11900, '2026-09-11');

        $this->assertSame(24.50, $price);
    }

    /**
     * @dataProvider approvedPriceProvider
     */
    public function test_spreadsheet_prices_match_production_product_names(
        string $product,
        float $saleUzs,
        float $factoryUzs
    ): void {
        $prices = (new ApprovedProductPriceService())->pricesFor($product);

        $this->assertSame($saleUzs, (float) $prices['sale_uzs']);
        $this->assertSame($factoryUzs, (float) $prices['factory_uzs']);
    }

    public function test_approved_price_sheet_uses_the_sample_usd_rate(): void
    {
        $service = new ApprovedProductPriceService();

        $this->assertSame(11900.0, $service->usdRate());
        $this->assertEqualsWithDelta(17.39495798, 207000 / $service->usdRate(), 0.00000001);
        $this->assertEqualsWithDelta(16.13445378, 192000 / $service->usdRate(), 0.00000001);
    }

    public function test_default_approved_price_rules_have_stable_unique_codes(): void
    {
        $rules = ApprovedProductPriceService::defaultRules();

        $this->assertCount(30, $rules);
        $this->assertCount(30, array_unique(array_column($rules, 'code')));
        $this->assertSame('0w20-4l', $rules[0]['code']);
        $this->assertSame('46lhm-20l', $rules[29]['code']);
    }

    public function test_monthly_report_includes_clients_that_only_have_cash_receipts(): void
    {
        $clientIds = CheckoutMonthExport::mergeReportClientIds([10, 20], [20, 148]);

        $this->assertSame([10, 20, 148], $clientIds->all());
    }

    /** @dataProvider excelPhoneProvider */
    public function test_client_phone_is_formatted_for_excel($phone, string $expected): void
    {
        $this->assertSame($expected, CheckoutMonthExport::formatPhoneForExcel($phone));
    }

    public function test_only_venox_bonus_is_removed_from_displayed_payment(): void
    {
        $breakdown = CheckoutMonthExport::paymentBreakdownUsd(2000, 500);

        $this->assertSame(2000.0, $breakdown['gross_usd']);
        $this->assertSame(1500.0, $breakdown['net_usd']);
        $this->assertSame(500.0, $breakdown['bonus_usd']);
        $this->assertSame(2000.0, $breakdown['net_usd'] + $breakdown['bonus_usd']);
    }

    public function test_fifo_quantities_are_balanced_to_the_gross_payment_at_approved_prices(): void
    {
        $quantities = CheckoutMonthExport::balanceApprovedQuantities(
            [100, 50],
            [20, 40],
            3600
        );

        $this->assertEqualsWithDelta(3600, $quantities[0] * 20 + $quantities[1] * 40, 0.000001);
        $this->assertEqualsWithDelta(2.0, $quantities[0] / $quantities[1], 0.000001);
    }

    public function test_balancing_keeps_whole_pieces_and_uses_the_nearest_total(): void
    {
        $whole = CheckoutMonthExport::balanceApprovedQuantities([2, 1], [200000, 100000], 600000);
        $nearest = CheckoutMonthExport::balanceApprovedQuantities([1], [200000], 450000);

        $this->assertSame([2.0, 2.0], $whole);
        $this->assertSame([2.0], $nearest);
        $this->assertLessThanOrEqual(100000, abs(450000 - $nearest[0] * 200000));
    }

    public function test_mirzoxid_like_mix_stays_within_real_carton_sizes(): void
    {
        $prices = [207000, 207500, 197000, 176000, 49000, 283000];
        $packages = [4, 4, 4, 4, 12, 4];
        $quantities = CheckoutMonthExport::balanceApprovedQuantities(
            [20, 12, 12, 12, 46, 16],
            $prices,
            2000 * 11900,
            $packages
        );

        $this->assertSame([28.0, 16.0, 16.0, 20.0, 48.0, 20.0], $quantities);
        $approvedTotal = 0.0;
        foreach ($quantities as $index => $qty) {
            $approvedTotal += $qty * $prices[$index];
        }
        $this->assertEqualsWithDelta(2000 * 11900, $approvedTotal, 0.000001);
        foreach ($quantities as $index => $qty) {
            $this->assertSame(0.0, fmod($qty, (float) $packages[$index]));
        }
    }

    /** @dataProvider packageQuantityProvider */
    public function test_package_quantity_comes_from_the_boss_workbook_sizes(string $name, int $expected): void
    {
        $this->assertSame($expected, CheckoutMonthExport::approvedPackageQuantity($name));
    }

    public function test_only_explicit_positive_sale_and_factory_prices_are_exportable(): void
    {
        $this->assertTrue(CheckoutMonthExport::hasCompleteApprovedPrices([
            'sale_uzs' => 207000,
            'factory_uzs' => 192000,
        ]));
        $this->assertFalse(CheckoutMonthExport::hasCompleteApprovedPrices([
            'sale_uzs' => 207000,
            'factory_uzs' => 0,
        ]));
        $this->assertFalse(CheckoutMonthExport::hasCompleteApprovedPrices(null));
    }

    public function test_venox_bonus_and_net_payment_reconstruct_the_approved_total(): void
    {
        $breakdown = CheckoutMonthExport::paymentBreakdownUsd(3600, 180);
        $quantities = CheckoutMonthExport::balanceApprovedQuantities([200], [20], $breakdown['gross_usd']);
        $approvedTotal = $quantities[0] * 20;

        $this->assertEqualsWithDelta(3600, $approvedTotal, 0.000001);
        $this->assertEqualsWithDelta($approvedTotal, $breakdown['net_usd'] + $breakdown['bonus_usd'], 0.000001);
    }

    public function test_opening_debt_reverses_the_visible_approved_price_formula(): void
    {
        $openingDebt = CheckoutMonthExport::openingDebtUsd(3204.43, 2094, 1800, 200);

        $this->assertEqualsWithDelta(3110.43, $openingDebt, 0.000001);
        $this->assertEqualsWithDelta(3204.43, $openingDebt + 2094 - 1800 - 200, 0.000001);
    }

    public function test_report_tracks_approved_and_actual_totals_separately(): void
    {
        $totals = CheckoutMonthExport::reportLineTotalsUsd(10, 65, 53);

        $this->assertSame(650.0, $totals['approved_total_usd']);
        $this->assertSame(530.0, $totals['actual_total_usd']);
    }

    public function test_payment_only_product_allocation_does_not_create_a_sale_total(): void
    {
        $totals = CheckoutMonthExport::reportLineTotalsUsd(10, 65, null);

        $this->assertSame(650.0, $totals['approved_total_usd']);
        $this->assertNull($totals['actual_total_usd']);
    }

    public function test_venox_cash_is_quantity_times_sale_and_factory_price_difference(): void
    {
        $venoxCash = CheckoutMonthExport::venoxCashUsd(10, 65, 53);

        $this->assertSame(120.0, $venoxCash);
    }

    public function test_venox_cash_total_uses_matching_product_price_indexes(): void
    {
        $venoxCash = CheckoutMonthExport::venoxCashTotalUsd(
            [10, 2],
            [65, 100],
            [53, 80]
        );

        $this->assertSame(160.0, $venoxCash);
    }

    public function test_monthly_export_line_cells_are_real_excel_formulas(): void
    {
        $this->assertSame([
            'unit_price_usd' => '=207000/$Q$2',
            'factory_price_usd' => '=192000/$Q$2',
            'markup_percent' => '=IFERROR((H3-I3)/I3,"")',
            'approved_total_usd' => '=G3*H3',
            'factory_total_usd' => '=G3*I3',
        ], CheckoutMonthExport::lineExcelFormulas(3, 207000, 192000));
    }

    public function test_saved_period_venox_bonus_is_used_when_linked_checkout_has_zero_bonus(): void
    {
        $linked = new Checkout(['id' => 10, 'date' => '2026-09-10', 'venox_bonus_percent' => 0]);
        $savedBonus = new Checkout(['id' => 11, 'date' => '2026-09-11', 'venox_bonus_percent' => 25]);

        $resolved = CheckoutMonthExport::venoxBonusCheckout(
            $linked,
            [$linked, $savedBonus],
            '2026-09-11'
        );

        $this->assertSame($savedBonus, $resolved);
    }

    public function test_monthly_export_client_totals_are_real_excel_formulas(): void
    {
        $this->assertSame([
            'closing_debt_usd' => '=E3+SUM(K3:K5)-M3-N3',
            'venox_cash_usd' => '=SUM(K3:K5)-SUM(L3:L5)',
        ], CheckoutMonthExport::clientExcelFormulas(3, 5));
    }

    public function test_displayed_paid_total_is_converted_to_uzs_with_report_rate(): void
    {
        $totalUzs = CheckoutMonthExport::paidTotalUzs(234325435 / 11078, 11078);

        $this->assertEqualsWithDelta(234325435, $totalUzs, 0.01);
    }

    public function approvedPriceProvider(): array
    {
        return [
            ['Масло моторное Venox 5w-30 SQ 4л Molygreen', 207000, 192000],
            ['Масло моторное Venox 5w-30 4л SP', 207500, 192000],
            ['Масло моторное Venox 10w-40 SN 4л', 197000, 184000],
            ['Масло моторное Venox 10W-40 API SL/CF 4л', 176000, 164000],
            ['Масло трансмиссионное ATF-V| Venox Dexron 5л', 283000, 261000],
            ['Масло трансмиссионное ATF-||| Venox 1л', 49000, 52200],
            ['Антифриз VENOX ANTIFREEZE -40°C красный 1л', 16800, 15600],
        ];
    }

    public function excelPhoneProvider(): array
    {
        return [
            ['770561836', '77 056 18 36'],
            ['+998 (77) 056-18-36', '77 056 18 36'],
            ['998770561836', '77 056 18 36'],
            ['12345', '12345'],
            [null, ''],
        ];
    }

    public function packageQuantityProvider(): array
    {
        return [
            ['Venox 5W-30 Molygreen 4L', 4],
            ['Venox 10W-40 Premium 3 л', 4],
            ['Venox Dexron 5L', 4],
            ['Venox ATF III 1L', 12],
            ['Venox 15W-40 20L', 1],
            ['Venox 10W-40 SN 208 Л', 1],
        ];
    }
}
