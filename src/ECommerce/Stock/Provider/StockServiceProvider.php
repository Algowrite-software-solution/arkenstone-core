<?php

namespace Arkenstone\Core\ECommerce\Stock\Provider;

use Arkenstone\Core\ECommerce\Contracts\StockServiceInterface;
use Arkenstone\Core\ECommerce\Stock\Services\StockService;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

class StockServiceProvider extends ServiceProvider
{
    protected string $name = 'Stock';

    /**
     * Called before routes are registered.
     *
     * Register any model bindings or pattern based filters.
     */
    public function boot(): void
    {
        parent::boot();

        // Load Stock module routes
        $this->loadRoutesFrom(__DIR__ . '/../routes/api.php');
    }

    public function register(): void
    {
        $this->app->singleton('stock', function () {
            return new StockService();
        });

        $this->app->bind(
            StockServiceInterface::class,
            StockService::class
        );
    }
}
