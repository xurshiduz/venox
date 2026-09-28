<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// TEMP: productionda checkouts jadvaliga mashina raqami ustunini qo‘shish uchun.
Route::post('/system/vehicle-number-migrate-7d4e9a2c6f31', function (Request $request) {
    abort_unless(hash_equals(
        '3321f55d0001fc892f0edd309f9fc3ffc2e61cdeccb60eba00214c0092573ca6',
        hash('sha256', (string) $request->bearerToken())
    ), 404);

    \Illuminate\Support\Facades\Artisan::call('migrate', [
        '--path' => 'database/migrations/2026_09_28_120000_add_vehicle_number_to_checkouts_table.php',
        '--force' => true,
    ]);

    return response()->json([
        'ok' => \Illuminate\Support\Facades\Schema::hasColumn('checkouts', 'vehicle_number'),
    ]);
})->middleware('throttle:1,1');

Route::post('/search_barcode', 'Backend\ProductController@api_search_barcode');

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


Route::post('/factory_checkin_save', 'Api\CheckinController@apiCheckinSave')
    ->withoutMiddleware(['throttle:api']);

Route::post('/factory-ledger/receipt', 'Api\FactoryLedgerController@receipt')
    ->withoutMiddleware(['throttle:api']);
Route::post('/factory-transfer/request', 'Api\CheckinController@apiCheckinSave')
    ->withoutMiddleware(['throttle:api']);
Route::get('/lidaz-ledger/supplier-return/{code}', 'Api\SupplierReturnRequestController@show')->where('code', '[0-9a-fA-F-]{36}')->withoutMiddleware(['throttle:api']);
Route::post('/lidaz-ledger/supplier-return/{code}/accepted', 'Api\SupplierReturnRequestController@accepted')->where('code', '[0-9a-fA-F-]{36}')->withoutMiddleware(['throttle:api']);
