<?php

namespace Arkenstone\Core\ECommerce\Product\Provider;

use Arkenstone\Core\ECommerce\Contracts\BrandServiceInterface;
use Arkenstone\Core\ECommerce\Product\Services\BrandService;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

class BrandServiceProvider extends ServiceProvider
{
    protected string $name = 'Brand';

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
        $this->app->singleton('brand', function () {
            return new BrandService();
        });

        $this->app->bind(
            BrandServiceInterface::class,
            BrandService::class
        );
    }
}
