<?php

namespace Arkenstone\Core\ECommerce\Order\Models;

use Arkenstone\Core\ECommerce\Product\Models\Product;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CartItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'cart_id',
        'product_id',
        'product_snapshot',
        'product_sku',
        'product_name',
        'quantity',
        'unit_price',
        'discount_type',
        'discount_value',
        'discount_amount',
        'tax_rate',
        'tax_amount',
        'subtotal',
        'total_price',
    ];

    protected $casts = [
        'product_snapshot' => 'array',
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'discount_type' => 'string',
        'discount_value' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_rate' => 'decimal:4',
        'tax_amount' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'total_price' => 'decimal:2',
    ];

    /**
     * Get the cart that owns the item
     */
    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }

    /**
     * Get the product
     * Note: Uses SET NULL on delete, so product can be deleted
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Create product snapshot
     * Preserves product data at time of adding to cart
     */
    public function createProductSnapshot(Product $product): array
    {
        return [
            'id' => $product->id,
            'name' => $product->name,
            'slug' => $product->slug,
            'sku' => $product->sku,
            'price' => $product->price,
            'sale_price' => $product->sale_price,
            'description' => $product->minified_description,
            'image' => $product->primaryImage(),
            'brand' => $product->brand?->name,
            'weight' => $product->weight,
            'dimensions' => [
                'length' => $product->length,
                'width' => $product->width,
                'height' => $product->height,
            ],
        ];
    }

    /**
     * Get product snapshot or create from current product
     */
    public function getSnapshotAttribute(): array
    {
        if ($this->product_snapshot) {
            return $this->product_snapshot;
        }

        if ($this->product) {
            return $this->createProductSnapshot($this->product);
        }

        return [];
    }

    /**
     * Calculate item total
     */
    public function calculateTotal(): void
    {
        $this->subtotal = $this->unit_price * $this->quantity;
        $this->total_price = $this->subtotal - $this->discount_amount + $this->tax_amount;
    }

    /**
     * Update quantity and recalculate totals
     */
    public function updateQuantity(int $quantity): void
    {
        $this->quantity = $quantity;
        $this->calculateTotal();
        $this->save();

        // Recalculate cart totals
        $this->cart->calculateTotals();
        $this->cart->save();
    }

    /**
     * Increase quantity
     */
    public function increaseQuantity(int $amount = 1): void
    {
        $this->updateQuantity($this->quantity + $amount);
    }

    /**
     * Decrease quantity
     */
    public function decreaseQuantity(int $amount = 1): void
    {
        $newQuantity = max(1, $this->quantity - $amount);
        $this->updateQuantity($newQuantity);
    }

    /**
     * Get display name from snapshot or product
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->snapshot['name'] ?? $this->product?->name ?? 'Unknown Product';
    }

    /**
     * Get display price from snapshot or product
     */
    public function getDisplayPriceAttribute(): float
    {
        return $this->snapshot['price'] ?? $this->product?->price ?? $this->unit_price;
    }

    /**
     * Get display image from snapshot or product
     */
    public function getDisplayImageAttribute(): ?string
    {
        return $this->snapshot['image'] ?? $this->product?->primaryImage()?->image_url;
    }

    /**
     * Scope: Get items by product
     */
    public function scopeForProduct($query, int $productId)
    {
        return $query->where('product_id', $productId);
    }
}
