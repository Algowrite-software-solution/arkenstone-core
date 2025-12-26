<?php

namespace Arkenstone\Core\ECommerce\Stock\Provider;

use Arkenstone\Core\ECommerce\Contracts\StockReservationServiceInterface;
use Arkenstone\Core\ECommerce\Stock\Services\StockReservationService;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

class StockReservationServiceProvider extends ServiceProvider
{
    protected string $name = 'StockReservation';

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
        $this->app->singleton('stock-reservation', function () {
            return new StockReservationService();
        });

        $this->app->bind(
            StockReservationServiceInterface::class,
            StockReservationService::class
        );
    }
}
