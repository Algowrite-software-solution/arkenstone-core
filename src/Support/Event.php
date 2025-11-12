<?php

namespace Arkenstone\Core\Support;

use Illuminate\Contracts\Events\Dispatcher;

class Event
{
    protected static $dispatcher;

    // Connect the Laravel event system
    public static function setDispatcher(Dispatcher $dispatcher)
    {
        static::$dispatcher = $dispatcher;
    }

    // Add a listener (like WordPress "add_filter")
    public static function hook($hookName, $callback)
    {
        static::$dispatcher->listen($hookName, $callback);
    }

    // Run all listeners and return the changed value
    public static function dispatch($hookName, $value, ...$args)
    {
        $responses = static::$dispatcher->dispatch($hookName, [$value, ...$args]);

        foreach ($responses as $response) {
            if ($response !== null) {
                $value = $response;
            }
        }

        return $value;
    }
}
