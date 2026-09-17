<?php

namespace Tests\Unit;

use App\Http\Controllers\Backend\ReorderRequestController;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ReorderRequestControllerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', ':memory:');
        DB::purge('sqlite');
        Carbon::setTestNow('2026-09-17 12:00:00');

        Schema::create('warehouses', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->unsignedBigInteger('dealer_id')->nullable();
            $table->unsignedTinyInteger('status')->default(1);
        });
        Schema::create('units', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
        });
        Schema::create('products', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('barcode')->nullable();
            $table->unsignedBigInteger('unit_id')->nullable();
        });
        Schema::create('checkins', function (Blueprint $table): void {
            $table->id();
            $table->date('date');
            $table->unsignedTinyInteger('type_id')->default(1);
            $table->unsignedTinyInteger('status')->default(1);
        });
        Schema::create('checkin_details', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('checkin_id');
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('warehouse_id');
            $table->decimal('qty', 14, 2);
            $table->unsignedTinyInteger('status')->default(1);
        });
        Schema::create('checkouts', function (Blueprint $table): void {
            $table->id();
            $table->date('date');
            $table->unsignedTinyInteger('status')->default(1);
        });
        Schema::create('checkout_details', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('checkout_id');
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('warehouse_id');
            $table->decimal('qty', 14, 2);
            $table->unsignedTinyInteger('status')->default(1);
        });
        Schema::create('warehouse_stocks', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('warehouse_id');
            $table->decimal('stock', 14, 2);
        });

        DB::table('warehouses')->insert(['id' => 1, 'name' => 'Asosiy ombor', 'dealer_id' => 1, 'status' => 1]);
        DB::table('units')->insert(['id' => 1, 'name' => 'dona']);
        DB::table('products')->insert([
            ['id' => 1, 'name' => 'Tez sotilgan mahsulot', 'barcode' => '1001', 'unit_id' => 1],
            ['id' => 2, 'name' => 'Sekin sotilgan mahsulot', 'barcode' => '1002', 'unit_id' => 1],
        ]);
        DB::table('checkins')->insert([
            ['id' => 1, 'date' => '2026-09-14', 'type_id' => 1, 'status' => 1],
            // Keyinroq qilingan qaytaruv haqiqiy oxirgi kirimni almashtirmasligi kerak.
            ['id' => 2, 'date' => '2026-09-16', 'type_id' => 4, 'status' => 1],
        ]);
        DB::table('checkin_details')->insert([
            ['id' => 1, 'checkin_id' => 1, 'product_id' => 1, 'warehouse_id' => 1, 'qty' => 500, 'status' => 1],
            ['id' => 2, 'checkin_id' => 1, 'product_id' => 2, 'warehouse_id' => 1, 'qty' => 500, 'status' => 1],
            ['id' => 3, 'checkin_id' => 2, 'product_id' => 1, 'warehouse_id' => 1, 'qty' => 5, 'status' => 1],
        ]);
        DB::table('checkouts')->insert(['id' => 1, 'date' => '2026-09-16', 'status' => 1]);
        DB::table('checkout_details')->insert([
            ['checkout_id' => 1, 'product_id' => 1, 'warehouse_id' => 1, 'qty' => 460, 'status' => 1],
            ['checkout_id' => 1, 'product_id' => 2, 'warehouse_id' => 1, 'qty' => 100, 'status' => 1],
        ]);
        DB::table('warehouse_stocks')->insert([
            ['product_id' => 1, 'warehouse_id' => 1, 'stock' => 40],
            ['product_id' => 2, 'warehouse_id' => 1, 'stock' => 400],
        ]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_it_only_creates_requests_for_fast_selling_low_stock_products(): void
    {
        $user = new User(['dealer_id' => 1]);
        $user->setRelation('roles', collect([(object) ['name' => 'admin']]));
        Auth::shouldReceive('user')->andReturn($user);

        $view = (new ReorderRequestController())->index(Request::create('/reorder_requests', 'GET'));
        $requests = $view->getData()['requests'];

        $this->assertSame(1, $requests->total());
        $this->assertSame('Tez sotilgan mahsulot', $requests->first()->product_name);
        $this->assertSame(3, $requests->first()->days_elapsed);
        $this->assertSame(460.0, (float) $requests->first()->sold_qty);
        $this->assertSame(40.0, (float) $requests->first()->remaining_qty);
        $this->assertSame(500.0, (float) $requests->first()->recommended_qty);
    }
}
