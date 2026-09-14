<?php

namespace Tests\Unit;

use App\Models\Returns;
use PHPUnit\Framework\TestCase;

class ReconciliationReturnTest extends TestCase
{
    public function test_return_total_uses_returned_quantity_and_original_unit_price(): void
    {
        $return = new Returns([
            'qty' => 3.5,
            'price' => 125000,
        ]);

        $this->assertSame(437500.0, $return->sumtotal());
    }
}
