<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;

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

Route::post('/ops/6275a32d9e7a5e894a48a8f13f138d8022b6afd24b84559b/migrate-transfer-flow', function () {
    foreach ([
        'database/migrations/2026_09_14_120000_add_factory_transfer_source_to_checkins_table.php',
        'database/migrations/2026_09_14_121000_create_supplier_return_requests_tables.php',
    ] as $path) {
        Artisan::call('migrate', ['--path' => $path, '--force' => true]);
    }
    return response()->json(['ok' => true, 'output' => Artisan::output()]);
})->withoutMiddleware(['throttle:api']);
