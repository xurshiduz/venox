<?php

namespace Tests\Unit;

use App\Services\AccountingCashReportService;
use Illuminate\Support\Collection;
use PHPUnit\Framework\TestCase;

class AccountingCashReportServiceTest extends TestCase
{
    public function test_partial_payments_continue_from_the_remaining_product_quantity(): void
    {
        $details = collect([
            $this->detail(1, 10, 'Tovar A', 100, 1000, 4),
            $this->detail(2, 20, 'Tovar B', 100, 500, 2),
            $this->detail(3, 30, 'Tovar C', 80, 1000, 6),
        ]);
        $service = new AccountingCashReportService();

        $first = $service->allocatePayment($details, 1800);
        $second = $service->allocatePayment($details, 700, 1800);

        $this->assertSame([10, 20, 30], $first['product_ids']);
        $this->assertEqualsWithDelta(100, $first['products'][0]['qty'], 0.000001);
        $this->assertEqualsWithDelta(100, $first['products'][1]['qty'], 0.000001);
        $this->assertEqualsWithDelta(24, $first['products'][2]['qty'], 0.000001);
        $this->assertEqualsWithDelta(744, $first['purchase_cost_usd'], 0.000001);
        $this->assertEqualsWithDelta(56, $second['products'][0]['qty'], 0.000001);
        $this->assertEqualsWithDelta(336, $second['purchase_cost_usd'], 0.000001);
        $this->assertEqualsWithDelta(0, $second['unallocated_usd'], 0.000001);
    }

    public function test_payment_above_checkout_total_is_reported_as_unallocated(): void
    {
        $details = collect([$this->detail(1, 10, 'Tovar A', 10, 100, 4)]);

        $result = (new AccountingCashReportService())->allocatePayment($details, 125);

        $this->assertEqualsWithDelta(10, $result['products'][0]['qty'], 0.000001);
        $this->assertEqualsWithDelta(25, $result['unallocated_usd'], 0.000001);
    }

    public function test_commission_shares_and_factory_remainder_are_calculated_from_payment(): void
    {
        $result = (new AccountingCashReportService())->splitPayment(2500, 5, 8, 25);

        $this->assertEqualsWithDelta(125, $result['kpi'], 0.000001);
        $this->assertEqualsWithDelta(200, $result['agent'], 0.000001);
        $this->assertEqualsWithDelta(625, $result['venox'], 0.000001);
        $this->assertEqualsWithDelta(1550, $result['factory'], 0.000001);
        $this->assertEqualsWithDelta(2500, array_sum($result), 0.000001);
    }

    public function test_example_payment_matches_cash_report_formula(): void
    {
        $result = (new AccountingCashReportService())->splitPayment(2500, 0, 8, 5);

        $this->assertEqualsWithDelta(0, $result['kpi'], 0.000001);
        $this->assertEqualsWithDelta(200, $result['agent'], 0.000001);
        $this->assertEqualsWithDelta(125, $result['venox'], 0.000001);
        $this->assertEqualsWithDelta(2175, $result['factory'], 0.000001);
    }

    public function test_linked_checkout_currency_corrects_legacy_receipt_currency(): void
    {
        $service = new AccountingCashReportService();

        // Eski receipt USD deb qolgan, lekin bog'langan savdo UZS va 1 USD = 12 000 UZS.
        $result = $service->paymentAmountToUsd(72000, 1, 12050, 2, 12000);

        $this->assertEqualsWithDelta(6, $result, 0.000001);
    }

    public function test_checkout_historical_usd_rate_is_used_for_usd_payment(): void
    {
        $service = new AccountingCashReportService();

        $result = $service->paymentAmountToUsd(2500, 2, 1, 1, 11900);

        $this->assertEqualsWithDelta(2500, $result, 0.000001);
    }

    public function test_unlinked_usd_receipt_is_not_divided_by_the_exchange_rate(): void
    {
        $service = new AccountingCashReportService();

        $result = $service->paymentAmountToUsd(2500, 1, 11800, null, null);

        $this->assertEqualsWithDelta(2500, $result, 0.000001);
    }

    public function test_unlinked_uzs_receipt_is_converted_to_usd(): void
    {
        $service = new AccountingCashReportService();

        $result = $service->paymentAmountToUsd(29500000, 2, 11800, null, null);

        $this->assertEqualsWithDelta(2500, $result, 0.000001);
    }

    public function test_legacy_unlinked_click_amount_is_not_mistaken_for_usd(): void
    {
        $service = new AccountingCashReportService();

        $result = $service->legacyUnlinkedPaymentToUsd(600000, 1, 12050, '600 000 Click');

        $this->assertEqualsWithDelta(49.792531, $result, 0.000001);
    }

    public function test_legacy_unlinked_explicit_usd_amount_stays_in_usd(): void
    {
        $service = new AccountingCashReportService();

        $result = $service->legacyUnlinkedPaymentToUsd(15000, 1, 12050, '15 000 $');

        $this->assertEqualsWithDelta(15000, $result, 0.000001);
    }

    public function test_fifo_parts_of_one_cash_receipt_are_combined_into_one_report_row(): void
    {
        $parts = collect([
            $this->reportRow(220, 'A-1', 'Spes', 1000, 0, 80, 50, 870, [
                ['id' => 10, 'name' => 'Tovar A', 'qty' => 40, 'unit' => 'dona'],
            ]),
            $this->reportRow(220, 'A-2', 'Spes', 1500, 0, 120, 75, 1305, [
                ['id' => 10, 'name' => 'Tovar A', 'qty' => 60, 'unit' => 'dona'],
                ['id' => 20, 'name' => 'Tovar B', 'qty' => 10, 'unit' => 'dona'],
            ]),
        ]);

        $row = (new AccountingCashReportService())->combineReceiptRows($parts);

        $this->assertSame(220, $row['receipt_id']);
        $this->assertSame('A-1, A-2', $row['checkout_code']);
        $this->assertSame('Spes', $row['scheme']);
        $this->assertEqualsWithDelta(2500, $row['payment_usd'], 0.000001);
        $this->assertEqualsWithDelta(200, $row['agent_amount'], 0.000001);
        $this->assertEqualsWithDelta(125, $row['venox'], 0.000001);
        $this->assertEqualsWithDelta(2175, $row['factory'], 0.000001);
        $this->assertEqualsWithDelta(100, $row['products'][0]['qty'], 0.000001);
        $this->assertSame([10, 20], $row['product_ids']);
    }

    private function reportRow(
        int $receiptId,
        string $checkoutCode,
        string $scheme,
        float $payment,
        float $kpi,
        float $agent,
        float $venox,
        float $factory,
        array $products
    ): array {
        return [
            'receipt_id' => $receiptId,
            'checkout_code' => $checkoutCode,
            'date' => '2026-07-08',
            'agent' => 'Akbar',
            'client' => 'Temur',
            'scheme' => $scheme,
            'scheme_group' => 'special',
            'products' => $products,
            'product_ids' => collect($products)->pluck('id')->all(),
            'purchase_cost_usd' => 100,
            'unallocated_usd' => 0,
            'payment_usd' => $payment,
            'kpi_percent' => 0,
            'agent_percent' => 8,
            'venox_percent' => 5,
            'kpi' => $kpi,
            'agent_amount' => $agent,
            'venox' => $venox,
            'factory' => $factory,
        ];
    }

    private function detail(int $id, int $productId, string $name, float $qty, float $lineTotal, float $unitCost): object
    {
        return (object) [
            'id' => $id,
            'product_id' => $productId,
            'qty' => $qty,
            'total_price' => $lineTotal,
            'tan_price' => $unitCost,
            'currency_type' => 1,
            'currency_type_price' => 1,
            'prodid' => (object) ['name' => $name, 'unitid' => (object) ['name' => 'dona']],
        ];
    }
}
