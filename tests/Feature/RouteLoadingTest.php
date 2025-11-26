<?php

namespace Arkenstone\Core\Tests\Feature;

use Arkenstone\Core\Tests\TestCase;
use Illuminate\Support\Facades\Route;

class RouteLoadingTest extends TestCase
{
    /** @test */
    public function it_loads_all_product_module_routes()
    {
        $registeredRoutes = collect(Route::getRoutes())->map(function ($route) {
            return [
                'name' => $route->getName(),
                'uri' => $route->uri(),
                'method' => implode('|', $route->methods()),
            ];
        });

        // Check for Product routes
        $this->assertTrue($registeredRoutes->contains('name', 'products.index'));
        $this->assertTrue($registeredRoutes->contains('name', 'products.store'));
        $this->assertTrue($registeredRoutes->contains('name', 'products.show'));
        $this->assertTrue($registeredRoutes->contains('name', 'products.update'));
        $this->assertTrue($registeredRoutes->contains('name', 'products.destroy'));

        // Check for Brand routes
        $this->assertTrue($registeredRoutes->contains('name', 'brands.index'));
        $this->assertTrue($registeredRoutes->contains('name', 'brands.store'));
        $this->assertTrue($registeredRoutes->contains('name', 'brands.show'));
        $this->assertTrue($registeredRoutes->contains('name', 'brands.update'));
        $this->assertTrue($registeredRoutes->contains('name', 'brands.destroy'));

        // Check for Category routes
        $this->assertTrue($registeredRoutes->contains('name', 'categories.index'));
        $this->assertTrue($registeredRoutes->contains('name', 'categories.store'));
        $this->assertTrue($registeredRoutes->contains('name', 'categories.show'));
        $this->assertTrue($registeredRoutes->contains('name', 'categories.update'));
        $this->assertTrue($registeredRoutes->contains('name', 'categories.destroy'));
        $this->assertTrue($registeredRoutes->contains('name', 'categories.children'));
        $this->assertTrue($registeredRoutes->contains('name', 'categories.roots'));

        // Check for Taxonomy routes
        $this->assertTrue($registeredRoutes->contains('name', 'taxonomies.index'));
        $this->assertTrue($registeredRoutes->contains('name', 'taxonomies.store'));
        $this->assertTrue($registeredRoutes->contains('name', 'taxonomies.show'));
        $this->assertTrue($registeredRoutes->contains('name', 'taxonomies.update'));
        $this->assertTrue($registeredRoutes->contains('name', 'taxonomies.destroy'));
        $this->assertTrue($registeredRoutes->contains('name', 'taxonomies.by-type'));

        // Check for ProductImage routes
        $this->assertTrue($registeredRoutes->contains('name', 'products.images.index'));
        $this->assertTrue($registeredRoutes->contains('name', 'product-images.store'));
        $this->assertTrue($registeredRoutes->contains('name', 'product-images.show'));
        $this->assertTrue($registeredRoutes->contains('name', 'product-images.update'));
        $this->assertTrue($registeredRoutes->contains('name', 'product-images.destroy'));
        $this->assertTrue($registeredRoutes->contains('name', 'products.images.set-primary'));
        $this->assertTrue($registeredRoutes->contains('name', 'products.primary-image'));

        // Check for ProductTaxonomy routes
        $this->assertTrue($registeredRoutes->contains('name', 'products.taxonomies.index'));
        $this->assertTrue($registeredRoutes->contains('name', 'taxonomies.products.index'));
        $this->assertTrue($registeredRoutes->contains('name', 'products.taxonomies.attach'));
        $this->assertTrue($registeredRoutes->contains('name', 'products.taxonomies.sync'));
        $this->assertTrue($registeredRoutes->contains('name', 'products.taxonomies.detach'));
    }

    /** @test */
    public function it_uses_correct_api_prefix_for_routes()
    {
        $routes = collect(Route::getRoutes())->filter(function ($route) {
            return $route->getName() && str_starts_with($route->getName(), 'products.');
        });

        foreach ($routes as $route) {
            $this->assertStringStartsWith('api/v1/', $route->uri());
        }
    }

    /** @test */
    public function it_applies_api_middleware_to_routes()
    {
        $route = Route::getRoutes()->getByName('products.index');

        $this->assertNotNull($route);
        $this->assertContains('api', $route->middleware());
    }

    /** @test */
    public function all_routes_have_unique_names()
    {
        $routeNames = collect(Route::getRoutes())
            ->map(fn($route) => $route->getName())
            ->filter()
            ->all();

        $uniqueNames = array_unique($routeNames);

        $this->assertCount(count($routeNames), $uniqueNames, 'Duplicate route names found');
    }

    /** @test */
    public function it_counts_total_registered_routes()
    {
        $productModuleRoutes = collect(Route::getRoutes())->filter(function ($route) {
            $name = $route->getName();
            return $name && (
                str_starts_with($name, 'products.') ||
                str_starts_with($name, 'brands.') ||
                str_starts_with($name, 'categories.') ||
                str_starts_with($name, 'taxonomies.') ||
                str_starts_with($name, 'product-images.')
            );
        });

        // Should have 35 routes total
        $this->assertGreaterThanOrEqual(35, $productModuleRoutes->count());
    }
}
