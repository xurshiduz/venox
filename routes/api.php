<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/search_barcode', 'Backend\ProductController@api_search_barcode');

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


Route::post('/factory_checkin_save', 'Api\CheckinController@apiCheckinSave')
    ->withoutMiddleware(['throttle:api']);