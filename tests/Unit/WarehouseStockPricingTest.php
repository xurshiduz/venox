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

    public function test_checkout_header_currency_wins_over_incorrect_legacy_detail_currency(): void
    {
        $saleUzs = Currency::documentAmountToUzs(
            16.50,
            1,
            11900,
            2,
            1
        );

        $this->assertSame(196350.0, $saleUzs);
        $this->assertEqualsWithDelta(60.416666, Currency::markupPercent(122400, $saleUzs), 0.0001);
    }

    public function test_markup_uses_unit_prices_and_does_not_depend_on_stock_quantity(): void
    {
        $costUzs = 89250;
        $saleUzs = 148750;

        $oneItemMarkup = Currency::markupPercent($costUzs, $saleUzs);
        $manyItemsMarkup = Currency::markupPercent($costUzs * 140, $saleUzs * 140);

        $this->assertEqualsWithDelta(66.666666, $oneItemMarkup, 0.0001);
        $this->assertEqualsWithDelta($oneItemMarkup, $manyItemsMarkup, 0.0001);
    }
}
