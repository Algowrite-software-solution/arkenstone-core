<?php

namespace Arkenstone\Core\ECommerce\Stock\Provider;

use Arkenstone\Core\ECommerce\Contracts\SupplierServiceInterface;
use Arkenstone\Core\ECommerce\Stock\Services\SupplierService;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

class SupplierServiceProvider extends ServiceProvider
{
    protected string $name = 'Supplier';

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
        $this->app->singleton('supplier', function () {
            return new SupplierService();
        });

        $this->app->bind(
            SupplierServiceInterface::class,
            SupplierService::class
        );
    }
}
