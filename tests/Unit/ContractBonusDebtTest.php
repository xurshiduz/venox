<?php

namespace Tests\Unit;

use App\Models\Checkout;
use App\Models\Client;
use App\Models\ContractBonusTransaction;
use App\Services\ContractBonusService;
use PHPUnit\Framework\TestCase;

class ContractBonusDebtTest extends TestCase
{
    public function test_tolik_angor_debt_offset_is_included_once_in_both_debt_reports(): void
    {
        $checkout = new Checkout([
            'id' => 165,
            'client_id' => 139,
            'currency_type' => 1,
            'currency_type_price' => 12050,
        ]);
        $checkout->setRelation('alldetails', collect([
            (object) ['total_price' => 4850],
        ]));

        $offset = new ContractBonusTransaction([
            'type' => 'debt_offset',
            'direction' => 'debit',
            'amount_usd' => 1100,
            'status' => true,
            'meta' => [
                'debt_allocations' => [[
                    'checkout_id' => 165,
                    'amount_usd' => 1100,
                    'amount_native' => 1100,
                ]],
            ],
        ]);

        $client = new Client([
            'id' => 139,
            'balance' => 0,
            'currency_type' => 1,
            'currency_type_price' => 12050,
        ]);
        $client->setRelation('checkouts', collect([$checkout]));
        $client->setRelation('cashReceipts', collect());
        $client->setRelation('checkins', collect());
        $client->setRelation('contractBonusTransactions', collect([$offset]));

        $service = new ContractBonusService();

        $this->assertSame(1100.0, $service->debtOffsetUsd($client, 1));
        $this->assertSame(13255000.0, $service->debtOffsetUzs($client));
        $this->assertSame(3750.0, $service->debtUsd($client));
        $this->assertSame(45187500.0, $service->debtUzs($client));
    }

    public function test_uzs_allocation_is_not_converted_twice(): void
    {
        $this->assertSame(13255000.0, ContractBonusService::allocationAmountUzs(13255000, 2, 12050));
    }
}
