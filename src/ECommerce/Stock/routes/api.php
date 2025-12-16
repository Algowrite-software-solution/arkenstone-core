<?php

use Arkenstone\Core\ECommerce\Stock\Http\Controllers\API\V1;
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

    // Stocks
    Route::get('stocks/low-stock', [V1\StockController::class, 'lowStock'])->name('stocks.low-stock');
    Route::get('stocks/out-of-stock', [V1\StockController::class, 'outOfStock'])->name('stocks.out-of-stock');
    Route::get('stocks/search', [V1\StockController::class, 'search'])->name('stocks.search');
    Route::post('stocks/check-availability', [V1\StockController::class, 'checkAvailability'])->name('stocks.check-availability');
    Route::post('stocks/{id}/adjust-quantity', [V1\StockController::class, 'adjustQuantity'])->name('stocks.adjust-quantity');
    Route::apiResource('stocks', V1\StockController::class);

    // Stock Reservations
    Route::post('stock-reservations/reserve', [V1\StockReservationController::class, 'reserve'])->name('stock-reservations.reserve');
    Route::post('stock-reservations/{id}/update-status', [V1\StockReservationController::class, 'updateStatus'])->name('stock-reservations.update-status');
    Route::post('stock-reservations/{id}/extend', [V1\StockReservationController::class, 'extend'])->name('stock-reservations.extend');
    Route::post('stock-reservations/{id}/release', [V1\StockReservationController::class, 'release'])->name('stock-reservations.release');
    Route::post('stock-reservations/{id}/commit', [V1\StockReservationController::class, 'commit'])->name('stock-reservations.commit');
    Route::post('stock-reservations/{id}/fulfill', [V1\StockReservationController::class, 'fulfill'])->name('stock-reservations.fulfill');
    Route::get('stock-reservations/stock/{stockId}/active', [V1\StockReservationController::class, 'activeByStock'])->name('stock-reservations.active-by-stock');
    Route::get('stock-reservations/by-reference', [V1\StockReservationController::class, 'byReference'])->name('stock-reservations.by-reference');
    Route::get('stock-reservations/{id}', [V1\StockReservationController::class, 'show'])->name('stock-reservations.show');

    // Suppliers
    Route::get('suppliers/search', [V1\SupplierController::class, 'search'])->name('suppliers.search');
    Route::apiResource('suppliers', V1\SupplierController::class);

    // Variants
    Route::apiResource('variants', V1\VariantController::class);

    // Variation Options
    Route::get('variants/{variantId}/options', [V1\VariationOptionController::class, 'byVariant'])->name('variants.options');
    Route::post('variation-options', [V1\VariationOptionController::class, 'store'])->name('variation-options.store');
    Route::get('variation-options/{id}', [V1\VariationOptionController::class, 'show'])->name('variation-options.show');
    Route::put('variation-options/{id}', [V1\VariationOptionController::class, 'update'])->name('variation-options.update');
    Route::delete('variation-options/{id}', [V1\VariationOptionController::class, 'destroy'])->name('variation-options.destroy');
});
