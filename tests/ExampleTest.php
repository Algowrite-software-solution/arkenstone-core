<?php

namespace Arkenstone\Core\Tests;

use Arkenstone\Core\Feature1;
use Arkenstone\Core\Facades\Feature1 as FacadesFeature1;
use Illuminate\Support\Facades\Log;

class ExampleTest extends TestCase
{
    public function testVersionReturnsString(): void
    {
        $pkg = new Feature1();
        $this->assertIsString($pkg->version());
    }
    
    public function test_something(): void
    {
        $pkg = FacadesFeature1::version();
        $this->assertIsString($pkg);
    }
    
    public function test_service(): void
    {
        $pkg = app()->make('utility');
        $this->assertIsString($pkg->generateRandomString());
        
        $pkg = app()->make('utility');
        $this->assertEquals("Arkenstone Test",$pkg->getName());
    }
}


