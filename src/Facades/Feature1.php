<?php

namespace Arkenstone\Core\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Arkenstone\Core\Feature1
 */
class Feature1 extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'feature1';
    }

    public static function version(): string
    {
        return 'Arkenstone Feature1 v0.1.0';
    }

    
}


