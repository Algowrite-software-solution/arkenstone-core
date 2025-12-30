<?php

namespace Arkenstone\Core\ECommerce\Stock\Provider;

use Arkenstone\Core\ECommerce\Contracts\VariantServiceInterface;
use Arkenstone\Core\ECommerce\Stock\Services\VariantService;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

class VariantServiceProvider extends ServiceProvider
{
    protected string $name = 'Variant';

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
        $this->app->singleton('variant', function () {
            return new VariantService();
        });

        $this->app->bind(
            VariantServiceInterface::class,
            VariantService::class
        );
    }
}
