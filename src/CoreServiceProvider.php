<?php

namespace Arkenstone\Core;

use Arkenstone\Core\ECommerce\Product\Provider\BrandServiceProvider;
use Arkenstone\Core\ECommerce\Product\Provider\CategoryServiceProvider;
use Arkenstone\Core\ECommerce\Product\Provider\ProductImageServiceProvider;
use Arkenstone\Core\ECommerce\Product\Provider\ProductServiceProvider;
use Arkenstone\Core\ECommerce\Product\Provider\ProductTaxonomyServiceProvider;
use Arkenstone\Core\ECommerce\Product\Provider\TaxonomyServiceProvider;
use Arkenstone\Core\ECommerce\Product\Provider\TaxonomyTypeServiceProvider;
use Arkenstone\Core\Services\UtilityService;
use Arkenstone\Core\Support\Event;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\ServiceProvider;

class CoreServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/arkenstone.php', 'arkenstone');

        $this->app->register(ProductServiceProvider::class);
        $this->app->register(BrandServiceProvider::class);
        $this->app->register(CategoryServiceProvider::class);
        $this->app->register(ProductImageServiceProvider::class);
        $this->app->register(ProductTaxonomyServiceProvider::class);
        $this->app->register(TaxonomyServiceProvider::class);
        $this->app->register(TaxonomyTypeServiceProvider::class);
        
        $this->app->singleton('utility', function () {
            return new UtilityService();
        });
    }

    public function boot(Dispatcher $dispatcher): void
    {
        $this->publishes([
            __DIR__ . '/../config/arkenstone.php' => config_path('arkenstone.php'),
        ], 'arkenstone-config');

        
        Event::setDispatcher($dispatcher); // attache the event to the app
    }
}


