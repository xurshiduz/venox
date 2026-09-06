<?php

namespace Tests\Unit;

use App\Models\Currency;
use PHPUnit\Framework\TestCase;

class WarehouseStockPricingTest extends TestCase
{
    public function test_usd_unit_sale_price_uses_the_document_rate_before_markup_is_calculated(): void
    {
        $costUzs = 89250;
        $saleUzs = Currency::toUzs(12.50, 1, 11900);

        $this->assertSame(148750.0, $saleUzs);
        $this->assertEqualsWithDelta(66.666666, Currency::markupPercent($costUzs, $saleUzs), 0.0001);
    }

    public function test_uzs_unit_price_is_not_converted_again(): void
    {
        $this->assertSame(566400.0, Currency::toUzs(566400, 2, 11900));
    }

    public function test_totals_are_unit_prices_multiplied_by_stock(): void
    {
        $stock = 140;
        $costUzs = 89250;
        $saleUzs = 148750;

        $this->assertSame(12495000, $costUzs * $stock);
        $this->assertSame(20825000, $saleUzs * $stock);
    }
}
