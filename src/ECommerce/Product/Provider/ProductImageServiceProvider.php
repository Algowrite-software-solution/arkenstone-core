<?php

namespace Arkenstone\Core\ECommerce\Product\Provider;

use Arkenstone\Core\ECommerce\Contracts\ProductImageServiceInterface;
use Arkenstone\Core\ECommerce\Product\Services\ProductImageService;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

class ProductImageServiceProvider extends ServiceProvider
{
    protected string $name = 'ProductImage';

    /**
     * Called before routes are registered.
     *
     * Register any model bindings or pattern based filters.
     */
    public function boot(): void
    {
        parent::boot();
    }

    public function register(): void
    {
        $this->app->singleton('product-image', function () {
            return new ProductImageService();
        });

        $this->app->bind(
            ProductImageServiceInterface::class,
            ProductImageService::class
        );
    }
}
