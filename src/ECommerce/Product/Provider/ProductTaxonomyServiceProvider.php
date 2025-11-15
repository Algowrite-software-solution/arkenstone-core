<?php

namespace Arkenstone\Core\ECommerce\Product\Provider;

use Arkenstone\Core\ECommerce\Product\Services\ProductTaxonomyService;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

class ProductTaxonomyServiceProvider extends ServiceProvider
{
    protected string $name = 'ProductTaxonomy';

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
        $this->app->singleton('product-taxonomy', function () {
            return new ProductTaxonomyService();
        });
    }
}
