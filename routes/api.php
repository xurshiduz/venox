<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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
