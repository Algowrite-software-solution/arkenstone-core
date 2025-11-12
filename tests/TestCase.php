<?php

namespace Arkenstone\Core\Tests;

use Arkenstone\Core\CoreServiceProvider;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

class TestCase extends OrchestraTestCase
{

    protected function getPackageProviders($app)
    {
        return [
            CoreServiceProvider::class,
        ];
    }




   /**
    * Get package aliases.
    *
    * Optional: If you have a facade
    *
    * @param  \Illuminate\Foundation\Application  $app
    * @return array
    */
   protected function getPackageAliases($app)
   {
       return [
           'feature1' => \Arkenstone\Core\Facades\Feature1::class,
       ];
   }
}
