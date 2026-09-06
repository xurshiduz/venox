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
