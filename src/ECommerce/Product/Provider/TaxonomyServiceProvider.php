<?php

namespace Arkenstone\Core\ECommerce\Product\Provider;

use Arkenstone\Core\ECommerce\Contracts\TaxonomyServiceInterface;
use Arkenstone\Core\ECommerce\Contracts\TaxonomyTypeServiceInterface;
use Arkenstone\Core\ECommerce\Product\Services\TaxonomyService;
use Arkenstone\Core\ECommerce\Product\Services\TaxonomyTypeService;
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
        // Bind TaxonomyService
        $this->app->singleton('taxonomy', function () {
            return new TaxonomyService();
        });
        
        $this->app->bind(TaxonomyServiceInterface::class, TaxonomyService::class);
        
        // Bind TaxonomyTypeService
        $this->app->singleton('taxonomy-type', function () {
            return new TaxonomyTypeService();
        });
        
        $this->app->bind(TaxonomyTypeServiceInterface::class, TaxonomyTypeService::class);
    }
}
