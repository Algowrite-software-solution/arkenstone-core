<?php

namespace Arkenstone\Core;

use Arkenstone\Core\ECommerce\Product\Provider\BrandServiceProvider;
use Arkenstone\Core\ECommerce\Product\Provider\CategoryServiceProvider;
use Arkenstone\Core\ECommerce\Product\Provider\ProductImageServiceProvider;
use Arkenstone\Core\ECommerce\Product\Provider\ProductServiceProvider;
use Arkenstone\Core\ECommerce\Product\Provider\ProductTaxonomyServiceProvider;
use Arkenstone\Core\ECommerce\Product\Provider\TaxonomyServiceProvider;
use Arkenstone\Core\ECommerce\Product\Provider\TaxonomyTypeServiceProvider;
use Arkenstone\Core\ECommerce\Stock\Provider\StockServiceProvider;
use Arkenstone\Core\ECommerce\Stock\Provider\StockReservationServiceProvider;
use Arkenstone\Core\ECommerce\Stock\Provider\SupplierServiceProvider;
use Arkenstone\Core\ECommerce\Stock\Provider\VariantServiceProvider;
use Arkenstone\Core\ECommerce\Stock\Provider\VariationOptionServiceProvider;
use Arkenstone\Core\Services\UtilityService;
use Arkenstone\Core\Support\Event;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\ServiceProvider;

class CoreServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/arkenstone.php', 'arkenstone');

        // Product Module Providers
        $this->app->register(ProductServiceProvider::class);
        $this->app->register(BrandServiceProvider::class);
        $this->app->register(CategoryServiceProvider::class);
        $this->app->register(ProductImageServiceProvider::class);
        $this->app->register(ProductTaxonomyServiceProvider::class);
        $this->app->register(TaxonomyServiceProvider::class);
        $this->app->register(TaxonomyTypeServiceProvider::class);

        // Stock Module Providers
        $this->app->register(StockServiceProvider::class);
        $this->app->register(StockReservationServiceProvider::class);
        $this->app->register(SupplierServiceProvider::class);
        $this->app->register(VariantServiceProvider::class);
        $this->app->register(VariationOptionServiceProvider::class);

        $this->app->singleton('utility', function () {
            return new UtilityService();
        });
    }

    public function boot(Dispatcher $dispatcher): void
    {
        // Initialize Event system
        Event::setDispatcher($dispatcher); // attache the event to the app

        // Publish configuration
        $this->publishes([
            __DIR__ . '/../config/arkenstone.php' => config_path('arkenstone.php'),
        ], 'arkenstone-config');

        // Publish migrations
        $this->publishes([
            __DIR__ . '/../database/migrations' => database_path('migrations'),
        ], 'arkenstone-migrations');

        // Publish seeders
        $this->publishes([
            __DIR__ . '/../database/seeders' => database_path('seeders'),
        ], 'arkenstone-seeders');


        //combined tag for all publishes
        $this->publishes([
            __DIR__ . '/../config/arkenstone.php' => config_path('arkenstone.php'),
            __DIR__ . '/../database/migrations' => database_path('migrations'),
            __DIR__ . '/../database/seeders' => database_path('seeders'),
        ], 'arkenstone');

        // Load migrations if running in package context (for testing)
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
    }
}


