<?php

namespace Arkenstone\Core\Tests\Stock\Unit\Services;

use Arkenstone\Core\Tests\TestCase;
use Arkenstone\Core\ECommerce\Stock\Models\Variant;
use Arkenstone\Core\ECommerce\Stock\Services\VariantService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class VariantServiceTest extends TestCase
{
    use RefreshDatabase;

    protected VariantService $variantService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->variantService = app()->make('variant');
    }

    /** @test */
    public function it_can_create_variant()
    {
        $data = ['name' => 'Color'];

        $variant = $this->variantService->createVariant($data);

        $this->assertInstanceOf(Variant::class, $variant);
        $this->assertEquals('Color', $variant->name);
        $this->assertDatabaseHas('variants', ['name' => 'Color']);
    }

    /** @test */
    public function it_can_get_variant_by_id()
    {
        $variant = Variant::factory()->create();

        $result = $this->variantService->getVariant($variant->id);

        $this->assertNotNull($result);
        $this->assertEquals($variant->id, $result->id);
    }

    /** @test */
    public function it_can_update_variant()
    {
        $variant = Variant::factory()->create(['name' => 'Size']);

        $updated = $this->variantService->updateVariant($variant->id, ['name' => 'Sizes']);

        $this->assertEquals('Sizes', $updated->name);
        $this->assertDatabaseHas('variants', ['id' => $variant->id, 'name' => 'Sizes']);
    }

    /** @test */
    public function it_can_delete_variant()
    {
        $variant = Variant::factory()->create();

        $result = $this->variantService->deleteVariant($variant->id);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('variants', ['id' => $variant->id]);
    }

    /** @test */
    public function it_can_get_all_variants()
    {
        Variant::factory()->count(3)->create();

        $variants = $this->variantService->getVariants();

        $this->assertCount(3, $variants);
    }
}
