<?php

namespace Arkenstone\Core\Tests\Product\Feature;

use Arkenstone\Core\ECommerce\Product\Models\Bundle;
use Arkenstone\Core\ECommerce\Product\Models\Product;
use Arkenstone\Core\ECommerce\Product\Services\BundleService;
use Arkenstone\Core\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Exception;

class BundleValidationTest extends TestCase
{
    use RefreshDatabase;

    protected BundleService $bundleService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bundleService = new BundleService();
    }

    /** @test */
    public function it_can_add_regular_product_to_bundle()
    {
        $bundle = Bundle::create(['name' => 'Main Bundle']);
        $product = Product::factory()->create(['name' => 'Regular Product']);

        $this->bundleService->addItems($bundle->id, [$product->id]);

        $this->assertDatabaseHas('bundle_items', [
            'bundle_id' => $bundle->id,
            'product_id' => $product->id,
        ]);
    }

    /** @test */
    public function it_cannot_add_a_bundle_as_a_child_of_another_bundle()
    {
        $parentBundle = Bundle::create(['name' => 'Parent Bundle']);
        $childBundle = Bundle::create(['name' => 'Child Bundle']);

        // Create a product that represents the child bundle
        $childBundleProduct = Product::factory()->create([
            'name' => 'Child Bundle Product',
            'bundle_id' => $childBundle->id
        ]);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage("A bundle can never be a child of another bundle.");

        $this->bundleService->addItems($parentBundle->id, [$childBundleProduct->id]);
    }

    /** @test */
    public function it_cannot_add_a_bundle_to_itself()
    {
        $bundle = Bundle::create(['name' => 'Self Bundle']);

        // Create a product that represents this bundle
        $bundleProduct = Product::factory()->create([
            'name' => 'Self Bundle Product',
            'bundle_id' => $bundle->id
        ]);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage("A bundle can never have itself as a child.");

        $this->bundleService->addItems($bundle->id, [$bundleProduct->id]);
    }
}
