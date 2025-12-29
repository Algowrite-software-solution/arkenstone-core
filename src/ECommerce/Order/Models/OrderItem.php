<?php

namespace Arkenstone\Core\ECommerce\Order\Models;

use Arkenstone\Core\ECommerce\Product\Models\Product;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'product_id',
        'product_snapshot',
        'quantity',
        'price',
        'discount_amount',
        'tax_amount',
        'total',
    ];

    protected $casts = [
        'product_snapshot' => 'array',
        'quantity' => 'integer',
        'price' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    /**
     * Get the order that owns the item
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the product
     * Note: Uses SET NULL on delete, relies on product_snapshot for historical data
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Create product snapshot
     * Preserves complete product data at time of order
     * CRITICAL for audit trail and handling product changes/deletions
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
            'details' => $product->minified_details,
            'image' => $product->primaryImage(),
            'images' => $product->images->pluck('image_url')->toArray(),
            'brand' => [
                'id' => $product->brand?->id,
                'name' => $product->brand?->name,
            ],
            'categories' => $product->categories->map(fn($cat) => [
                'id' => $cat->id,
                'name' => $cat->name,
            ])->toArray(),
            'weight' => $product->weight,
            'dimensions' => [
                'length' => $product->length,
                'width' => $product->width,
                'height' => $product->height,
            ],
            'timestamp' => now()->toIso8601String(),
        ];
    }

    /**
     * Get product snapshot
     * Always use snapshot for display, not live product data
     */
    public function getSnapshotAttribute(): array
    {
        if ($this->product_snapshot) {
            return $this->product_snapshot;
        }

        // Fallback: if snapshot missing and product exists, create it
        if ($this->product) {
            return $this->createProductSnapshot($this->product);
        }

        return [];
    }

    /**
     * Get display name from snapshot (primary) or product (fallback)
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->snapshot['name'] ?? $this->product?->name ?? 'Product Unavailable';
    }

    /**
     * Get display SKU from snapshot
     */
    public function getDisplaySkuAttribute(): ?string
    {
        return $this->snapshot['sku'] ?? $this->product?->sku;
    }

    /**
     * Get display image from snapshot
     */
    public function getDisplayImageAttribute(): ?string
    {
        return $this->snapshot['image'] ?? $this->product?->primaryImage()?->image_url;
    }

    /**
     * Get display images (all) from snapshot
     */
    public function getDisplayImagesAttribute(): array
    {
        return $this->snapshot['images'] ?? ($this->product ? $this->product->images->pluck('image_url')->toArray() : []);
    }

    /**
     * Get display brand name from snapshot
     */
    public function getDisplayBrandAttribute(): ?string
    {
        return $this->snapshot['brand']['name'] ?? $this->product?->brand?->name;
    }

    /**
     * Get subtotal (before tax/discount)
     */
    public function getSubtotalAttribute(): float
    {
        return $this->price * $this->quantity;
    }

    /**
     * Calculate line item total
     */
    public function calculateTotal(): void
    {
        $this->total = ($this->price * $this->quantity) - $this->discount_amount + $this->tax_amount;
    }

    /**
     * Scope: Get items by product
     */
    public function scopeForProduct($query, int $productId)
    {
        return $query->where('product_id', $productId);
    }

    /**
     * Scope: Get items with snapshot data
     */
    public function scopeWithSnapshot($query)
    {
        return $query->whereNotNull('product_snapshot');
    }
}
