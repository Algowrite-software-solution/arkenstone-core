<?php

namespace Arkenstone\Core\ECommerce\Product\Provider;


use Arkenstone\Core\ECommerce\Contracts\TaxonomyTypeServiceInterface;
use Arkenstone\Core\ECommerce\Product\Services\TaxonomyTypeService; 
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

class TaxonomyTypeServiceProvider extends ServiceProvider
{
    protected string $name = 'TaxonomyType';

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
        $this->app->singleton('taxonomy-type', function () {
            return new TaxonomyTypeService();
        });

        $this->app->bind(
            TaxonomyTypeServiceInterface::class,
            TaxonomyTypeService::class
        );
    }
}