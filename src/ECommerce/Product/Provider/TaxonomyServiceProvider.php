<?php

namespace Arkenstone\Core\ECommerce\Product\Provider;

use Arkenstone\Core\ECommerce\Product\Services\TaxonomyService;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

class TaxonomyServiceProvider extends ServiceProvider
{
    protected string $name = 'Taxonomy';

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
        $this->app->singleton('taxonomy', function () {
            return new TaxonomyService();
        });
    }
}
