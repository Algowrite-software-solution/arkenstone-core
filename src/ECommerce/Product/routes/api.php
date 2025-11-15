<?php

use Arkenstone\Core\ECommerce\Product\Http\Controllers\API\V1;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Product API Routes
|--------------------------------------------------------------------------
|
| Here are the API routes for the Product module. These routes are loaded
| by the ProductServiceProvider and are assigned the "api" middleware group.
|
*/

Route::prefix('api/v1')->middleware('api')->group(function () {

    // Products
    Route::apiResource('products', V1\ProductController::class);

    // Brands
    Route::apiResource('brands', V1\BrandController::class);

    // Categories
    Route::apiResource('categories', V1\CategoryController::class);
    Route::get('categories/{id}/children', [V1\CategoryController::class, 'children'])->name('categories.children');
    Route::get('categories/roots', [V1\CategoryController::class, 'roots'])->name('categories.roots');

    // Taxonomies
    Route::apiResource('taxonomies', V1\TaxonomyController::class);
    Route::get('taxonomies/type/{typeId}', [V1\TaxonomyController::class, 'byType'])->name('taxonomies.by-type');

    // Product Images
    Route::get('products/{productId}/images', [V1\ProductImageController::class, 'index'])->name('products.images.index');
    Route::post('product-images', [V1\ProductImageController::class, 'store'])->name('product-images.store');
    Route::get('product-images/{id}', [V1\ProductImageController::class, 'show'])->name('product-images.show');
    Route::put('product-images/{id}', [V1\ProductImageController::class, 'update'])->name('product-images.update');
    Route::delete('product-images/{id}', [V1\ProductImageController::class, 'destroy'])->name('product-images.destroy');
    Route::post('products/{productId}/images/{imageId}/set-primary', [V1\ProductImageController::class, 'setPrimary'])->name('products.images.set-primary');
    Route::get('products/{productId}/primary-image', [V1\ProductImageController::class, 'getPrimary'])->name('products.primary-image');
});
