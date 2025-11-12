<?php

namespace Arkenstone\Core;


use Arkenstone\Core\Feature1;
use Arkenstone\Core\Services\UtilityService;
use Arkenstone\Core\Support\Event;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\ServiceProvider;

class CoreServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/arkenstone.php', 'arkenstone');

        $this->app->singleton('arkenstone', function () {
            return new Feature1();
        });
        
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


