<?php

namespace Tests\Unit;

use App\Models\Transfer;
use PHPUnit\Framework\TestCase;

class ClientTransferModeTest extends TestCase
{
    public function test_it_distinguishes_client_and_warehouse_transfers(): void
    {
        $clientTransfer = new Transfer(['transfer_type' => 'client']);
        $warehouseTransfer = new Transfer(['transfer_type' => 'warehouse']);

        $this->assertTrue($clientTransfer->isClientTransfer());
        $this->assertFalse($warehouseTransfer->isClientTransfer());
    }
}
