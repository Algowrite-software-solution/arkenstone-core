<?php

namespace Arkenstone\Core\Tests\Unit\Models;

use Arkenstone\Core\ECommerce\Product\Enum\DiscountType;
use Arkenstone\Core\ECommerce\Product\Models\Product;
use Arkenstone\Core\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_implements_product_contract()
    {
        $product = Product::factory()->create();

        $this->assertInstanceOf(\Arkenstone\Core\ECommerce\Contracts\Product\ProductContract::class, $product);
    }

    /** @test */
    public function it_auto_generates_slug_from_name_on_creation()
    {
        $product = Product::factory()->create([
            'name' => 'Test Product Name',
            'slug' => null,
        ]);

        $this->assertEquals('test-product-name', $product->slug);
    }

    /** @test */
    public function it_respects_manually_set_slug()
    {
        $product = Product::factory()->create([
            'name' => 'Test Product',
            'slug' => 'custom-slug',
        ]);

        $this->assertEquals('custom-slug', $product->slug);
    }

    /** @test */
    public function has_discount_returns_false_when_no_discount_set()
    {
        $product = Product::factory()->create([
            'price' => 100.00,
            'discount_type' => null,
            'discount_value' => null,
        ]);

        $this->assertFalse($product->hasDiscount());
    }

    /** @test */
    public function has_discount_returns_false_when_discount_value_is_zero()
    {
        $product = Product::factory()->create([
            'price' => 100.00,
            'discount_type' => DiscountType::PERCENTAGE,
            'discount_value' => 0,
        ]);

        $this->assertFalse($product->hasDiscount());
    }

    /** @test */
    public function has_discount_returns_true_when_discount_is_set()
    {
        $product = Product::factory()->create([
            'price' => 100.00,
            'discount_type' => DiscountType::PERCENTAGE,
            'discount_value' => 10,
        ]);

        $this->assertTrue($product->hasDiscount());
    }

    /** @test */
    public function sale_price_is_null_when_no_discount()
    {
        $product = Product::factory()->create([
            'price' => 100.00,
            'discount_type' => null,
            'discount_value' => null,
        ]);

        $this->assertNull($product->sale_price);
    }

    /** @test */
    public function sale_price_calculates_correctly_for_percentage_discount()
    {
        $product = Product::factory()->create([
            'price' => 100.00,
            'discount_type' => DiscountType::PERCENTAGE,
            'discount_value' => 20, // 20% off
        ]);

        $this->assertEquals(80.00, $product->sale_price);
    }

    /** @test */
    public function sale_price_calculates_correctly_for_fixed_amount_discount()
    {
        $product = Product::factory()->create([
            'price' => 100.00,
            'discount_type' => DiscountType::FIXED_AMOUNT,
            'discount_value' => 25, // $25 off
        ]);

        $this->assertEquals(75.00, $product->sale_price);
    }

    /** @test */
    public function sale_price_never_goes_below_zero_for_fixed_amount()
    {
        $product = Product::factory()->create([
            'price' => 50.00,
            'discount_type' => DiscountType::FIXED_AMOUNT,
            'discount_value' => 75, // $75 off (more than price)
        ]);

        $this->assertEquals(0.00, $product->sale_price);
    }

    /** @test */
    public function percentage_discount_at_100_percent_makes_product_free()
    {
        $product = Product::factory()->create([
            'price' => 100.00,
            'discount_type' => DiscountType::PERCENTAGE,
            'discount_value' => 100, // 100% off
        ]);

        $this->assertEquals(0.00, $product->sale_price);
    }

    /** @test */
    public function sale_price_rounds_to_two_decimal_places()
    {
        $product = Product::factory()->create([
            'price' => 99.99,
            'discount_type' => DiscountType::PERCENTAGE,
            'discount_value' => 33.33, // Creates decimal precision scenario
        ]);

        // 99.99 - (99.99 * 0.3333) = 66.66333...
        $this->assertEquals(66.66, round($product->sale_price, 2));
    }

    /** @test */
    public function it_casts_discount_type_to_enum()
    {
        $product = Product::factory()->create([
            'discount_type' => DiscountType::PERCENTAGE,
        ]);

        $this->assertInstanceOf(DiscountType::class, $product->discount_type);
        $this->assertEquals(DiscountType::PERCENTAGE, $product->discount_type);
    }

    /** @test */
    public function it_casts_discount_value_to_decimal()
    {
        $product = Product::factory()->create([
            'discount_value' => 10.5,
        ]);

        $this->assertEquals('10.50', $product->discount_value);
    }

    /** @test */
    public function it_casts_price_to_decimal()
    {
        $product = Product::factory()->create([
            'price' => 99.9,
        ]);

        $this->assertEquals('99.90', $product->price);
    }

    /** @test */
    public function it_casts_is_active_to_boolean()
    {
        $product = Product::factory()->create([
            'is_active' => 1,
        ]);

        $this->assertIsBool($product->is_active);
        $this->assertTrue($product->is_active);
    }
}
