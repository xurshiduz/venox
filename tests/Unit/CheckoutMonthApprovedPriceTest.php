<?php

namespace Tests\Unit;

use App\Exports\CheckoutMonthExport;
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
}
