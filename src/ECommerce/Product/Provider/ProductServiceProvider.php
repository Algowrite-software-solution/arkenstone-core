<?php

namespace Arkenstone\Core\ECommerce\Product\Provider;

use Arkenstone\Core\ECommerce\Contracts\ProductServiceInterface;
use Arkenstone\Core\ECommerce\Product\Services\ProductService;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

class ProductServiceProvider extends ServiceProvider
{
    protected string $name = 'Product';

    /**
     * Called before routes are registered.
     *
     * Register any model bindings or pattern based filters.
     */
    public function boot(): void
    {
        parent::boot();

        // Load Product module routes
        $this->loadRoutesFrom(__DIR__ . '/../routes/api.php');
    }

    public function register(): void
    {
        $this->app->singleton('product', function () {
            return new ProductService();
        });

        $this->app->bind(
            ProductServiceInterface::class,
            ProductService::class
        );
    }
}
