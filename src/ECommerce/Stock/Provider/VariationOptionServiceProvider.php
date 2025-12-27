<?php

namespace Arkenstone\Core\ECommerce\Stock\Provider;

use Arkenstone\Core\ECommerce\Contracts\VariationOptionServiceInterface;
use Arkenstone\Core\ECommerce\Stock\Services\VariationOptionService;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

class VariationOptionServiceProvider extends ServiceProvider
{
    protected string $name = 'VariationOption';

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
        $this->app->singleton('variation-option', function () {
            return new VariationOptionService();
        });

        $this->app->bind(
            VariationOptionServiceInterface::class,
            VariationOptionService::class
        );
    }
}
