<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Order API Routes
|--------------------------------------------------------------------------
|
| Here are the API routes for the Order module. These routes are loaded
| by the OrderServiceProvider and are assigned the "api" middleware group.
|
*/

Route::prefix('api/v1')->middleware('api')->group(function () {

    // Example Order routes (implement when you create Order controllers)
    // Route::apiResource('orders', V1\OrderController::class);
    // Route::get('orders/{id}/items', [V1\OrderController::class, 'items']);
    // Route::post('orders/{id}/cancel', [V1\OrderController::class, 'cancel']);

});
