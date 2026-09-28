<?php

namespace Tests\Unit;

use App\Models\Checkout;
use App\Models\CheckoutType;
use App\Models\Client;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ReconciliationSupplierReturnTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', ':memory:');
        DB::purge('sqlite');
        app()->setLocale('ru');

        Schema::create('checkout_details', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('checkout_id');
            $table->decimal('total_price', 14, 2);
        });

        DB::table('checkout_details')->insert(['checkout_id' => 704, 'total_price' => 44676000]);
    }

    public function test_only_lidaz_supplier_is_factory_supplier(): void
    {
        $this->assertTrue((new Client(['name' => 'LIDAZ MCHJ', 'is_supplier' => 1]))->isFactorySupplier());
        $this->assertFalse((new Client(['name' => 'LIDAZ MCHJ', 'is_supplier' => null]))->isFactorySupplier());
        $this->assertFalse((new Client(['name' => 'Simma ishchilar', 'is_supplier' => 1]))->isFactorySupplier());
    }

    public function test_checkout_to_lidaz_is_shown_as_return_in_act(): void
    {
        $html = $this->renderAct(new Client(['name' => 'LIDAZ MCHJ', 'is_supplier' => 1]));

        $this->assertStringContainsString('Возврат товара', $html);
        $this->assertStringContainsString('Возврат товара поставщику; накладная №26000704;', $html);
        $this->assertStringNotContainsString('Оптовик', $html);
        // Summa avvalgidek debetda qoladi.
        $this->assertMatchesRegularExpression('/Обороты за период<\/td>\s*<td[^>]*>44 676 000\.00<\/td>\s*<td[^>]*>0\.00<\/td>/u', $html);
    }

    public function test_checkout_to_regular_client_keeps_its_type(): void
    {
        $html = $this->renderAct(new Client(['name' => 'Oddiy mijoz', 'is_customer' => 1]));

        $this->assertStringContainsString('Оптовик', $html);
        $this->assertStringContainsString('Накладная - счет фактура №26000704;', $html);
        $this->assertStringNotContainsString('Возврат', $html);
    }

    private function renderAct(Client $client): string
    {
        $checkout = new Checkout([
            'checkout_tip_id' => 4,
            'number_work' => '26000704',
            'date' => '2026-09-24',
        ]);
        $checkout->id = 704;
        $checkout->setRelation('returns', collect());
        $checkout->setRelation('checktypeid', new CheckoutType(['name_ru' => 'Оптовик']));

        return view('backend.reconciliation_act.excel_v2', [
            'data' => collect([$checkout]),
            'from' => '2026-09-01',
            'to' => '2026-09-30',
            'client' => $client,
            'comp' => 'VENOX',
            'startSaldo' => 0,
        ])->render();
    }
}
