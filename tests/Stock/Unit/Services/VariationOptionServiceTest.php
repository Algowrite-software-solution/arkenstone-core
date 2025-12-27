<?php

namespace Arkenstone\Core\Tests\Stock\Unit\Services;

use Arkenstone\Core\Tests\TestCase;
use Arkenstone\Core\ECommerce\Stock\Models\Variant;
use Arkenstone\Core\ECommerce\Stock\Models\VariationOption;
use Arkenstone\Core\ECommerce\Stock\Services\VariationOptionService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class VariationOptionServiceTest extends TestCase
{
    use RefreshDatabase;

    protected VariationOptionService $optionService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->optionService = app()->make('variation-option');
    }

    /** @test */
    public function it_can_create_variation_option()
    {
        $variant = Variant::factory()->create();

        $data = [
            'variant_id' => $variant->id,
            'name' => 'Red',
        ];

        $option = $this->optionService->createOption($data);

        $this->assertInstanceOf(VariationOption::class, $option);
        $this->assertEquals('Red', $option->name);
        $this->assertEquals($variant->id, $option->variant_id);
        $this->assertDatabaseHas('variation_options', ['name' => 'Red']);
    }

    /** @test */
    public function it_can_get_option_by_id()
    {
        $option = VariationOption::factory()->create();

        $result = $this->optionService->getOption($option->id);

        $this->assertNotNull($result);
        $this->assertEquals($option->id, $result->id);
    }

    /** @test */
    public function it_can_update_option()
    {
        $option = VariationOption::factory()->create(['name' => 'Small']);

        $updated = $this->optionService->updateOption($option->id, ['name' => 'Medium']);

        $this->assertEquals('Medium', $updated->name);
        $this->assertDatabaseHas('variation_options', [
            'id' => $option->id,
            'name' => 'Medium',
        ]);
    }

    /** @test */
    public function it_can_delete_option()
    {
        $option = VariationOption::factory()->create();

        $result = $this->optionService->deleteOption($option->id);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('variation_options', ['id' => $option->id]);
    }

    /** @test */
    public function it_can_get_options_by_variant()
    {
        $variant = Variant::factory()->create();
        VariationOption::factory()->count(3)->create(['variant_id' => $variant->id]);
        VariationOption::factory()->create(); // Different variant

        $options = $this->optionService->getOptionsByVariant($variant->id);

        $this->assertCount(3, $options);
    }
}
