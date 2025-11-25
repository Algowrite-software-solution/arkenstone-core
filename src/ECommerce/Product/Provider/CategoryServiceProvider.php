<?php

namespace Arkenstone\Core\ECommerce\Product\Provider;

use Arkenstone\Core\ECommerce\Contracts\CategoryServiceInterface;
use Arkenstone\Core\ECommerce\Product\Services\CategoryService;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

class CategoryServiceProvider extends ServiceProvider
{
    protected string $name = 'Category';

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
        $this->app->singleton('category', function () {
            return new CategoryService();
        });

        $this->app->bind(
            CategoryServiceInterface::class,
            CategoryService::class
        );
    }
}
