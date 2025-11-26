<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Stock API Routes
|--------------------------------------------------------------------------
|
| Here are the API routes for the Stock module. These routes are loaded
| by the StockServiceProvider and are assigned the "api" middleware group.
|
*/

Route::prefix('api/v1')->middleware('api')->group(function () {

    // Example Stock routes (implement when you create Stock controllers)
    // Route::apiResource('stock', V1\StockController::class);
    // Route::post('stock/{productId}/adjust', [V1\StockController::class, 'adjust']);
    // Route::get('stock/low-stock', [V1\StockController::class, 'lowStock']);

});
