<?php

namespace Tests\Unit;

use App\Services\CheckoutDayReportService;
use PHPUnit\Framework\TestCase;

class CheckoutDayReportServiceTest extends TestCase
{
    public function test_debt_after_sale_subtracts_payments_and_returns(): void
    {
        $this->assertEqualsWithDelta(
            1250.0,
            CheckoutDayReportService::debtAfterSale(1000, 500, 200, 50),
            0.000001
        );
    }

    public function test_debt_after_sale_preserves_prepayment_credit(): void
    {
        $this->assertSame(50.0, CheckoutDayReportService::debtAfterSale(-100, 200, 50, 0));
    }

    public function test_debt_after_sale_never_displays_negative_debt(): void
    {
        $this->assertSame(0.0, CheckoutDayReportService::debtAfterSale(100, 50, 200, 0));
    }
}
