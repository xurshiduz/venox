<?php

namespace Tests\Unit;

use App\Exports\CheckoutMonthExport;
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

    public function test_kpi_and_venox_bonus_are_included_in_monthly_bonus_expenses(): void
    {
        $breakdown = CheckoutMonthExport::paymentBreakdownUsd(2000, 100, 200);

        $this->assertSame(2000.0, $breakdown['gross_usd']);
        $this->assertSame(1700.0, $breakdown['net_usd']);
        $this->assertSame(300.0, $breakdown['bonus_usd']);
        $this->assertSame(2000.0, $breakdown['net_usd'] + $breakdown['bonus_usd']);
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
}
