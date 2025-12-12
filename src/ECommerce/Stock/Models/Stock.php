<?php

namespace Arkenstone\Core\ECommerce\Stock\Models;

use Arkenstone\Core\ECommerce\Product\Models\Product;
use Arkenstone\Core\ECommerce\Product\Models\ProductImage;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Stock extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'product_id',
        'sku',
        'barcode',
        'price',
        'cost',
        'weight',
        'quantity_on_hand',
        'quantity_reserved',
        'min_stock_level',
        'supplier_id',
        'image_url_id',
        'status',
    ];

    protected $casts = [
        'product_id' => 'integer',
        'supplier_id' => 'integer',
        'image_url_id' => 'integer',
        'price' => 'decimal:2',
        'cost' => 'decimal:2',
        'weight' => 'decimal:3',
        'quantity_on_hand' => 'integer',
        'quantity_reserved' => 'integer',
        'min_stock_level' => 'integer',
    ];

    protected $appends = ['quantity_available'];

    /**
     * Get the product that owns the stock.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the supplier that owns the stock.
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * Get the image for the stock.
     */
    public function image(): BelongsTo
    {
        return $this->belongsTo(ProductImage::class, 'image_url_id');
    }

    /**
     * Get the variation options for the stock.
     */
    public function variationOptions(): BelongsToMany
    {
        return $this->belongsToMany(VariationOption::class, 'stock_variant_options');
    }

    /**
     * Get the reservations for the stock.
     */
    public function reservations(): HasMany
    {
        return $this->hasMany(StockReservation::class);
    }

    /**
     * Get the available quantity (computed attribute).
     */
    protected function quantityAvailable(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->quantity_on_hand - $this->quantity_reserved,
        );
    }

    /**
     * Check if stock is available.
     */
    public function isAvailable(): bool
    {
        return $this->quantity_available > 0 && $this->status === 'active' && !$this->trashed();
    }

    /**
     * Check if stock is low.
     */
    public function isLowStock(): bool
    {
        return $this->quantity_available <= $this->min_stock_level;
    }

    /**
     * Get available quantity.
     */
    public function getAvailableQuantity(): int
    {
        return max(0, $this->quantity_available);
    }

    /**
     * Get reserved quantity from active reservations.
     */
    public function getReservedQuantity(): int
    {
        return $this->reservations()
            ->whereIn('status', ['pending', 'checking_out', 'committed'])
            ->sum('quantity');
    }

    /**
     * Check if can reserve quantity.
     */
    public function canReserve(int $quantity): bool
    {
        return $this->isAvailable() && $this->quantity_available >= $quantity;
    }

    /**
     * Reserve stock for cart/order.
     */
    public function reserve(int $quantity, array $reference): StockReservation
    {
        $reservation = $this->reservations()->create([
            'quantity' => $quantity,
            'status' => 'pending',
            'reference_type' => $reference['type'] ?? null,
            'reference_id' => $reference['id'] ?? null,
            'expires_at' => now()->addMinutes(10),
        ]);

        $this->increment('quantity_reserved', $quantity);

        return $reservation;
    }

    /**
     * Adjust quantity on hand.
     */
    public function adjustQuantity(int $quantity, string $reason = 'adjustment'): bool
    {
        $this->quantity_on_hand += $quantity;
        return $this->save();
    }

    /**
     * Scope a query to only include active stocks.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope a query to filter by SKU.
     */
    public function scopeBySku(Builder $query, string $sku): Builder
    {
        return $query->where('sku', $sku);
    }

    /**
     * Scope a query to filter by product.
     */
    public function scopeByProduct(Builder $query, int $productId): Builder
    {
        return $query->where('product_id', $productId);
    }

    /**
     * Scope a query to filter by supplier.
     */
    public function scopeBySupplier(Builder $query, int $supplierId): Builder
    {
        return $query->where('supplier_id', $supplierId);
    }

    /**
     * Scope a query to only include low stock items.
     */
    public function scopeLowStock(Builder $query): Builder
    {
        return $query->whereRaw('(quantity_on_hand - quantity_reserved) <= min_stock_level');
    }

    /**
     * Scope a query to only include out of stock items.
     */
    public function scopeOutOfStock(Builder $query): Builder
    {
        return $query->whereRaw('(quantity_on_hand - quantity_reserved) <= 0');
    }

    /**
     * Scope a query to only include in stock items.
     */
    public function scopeInStock(Builder $query): Builder
    {
        return $query->whereRaw('(quantity_on_hand - quantity_reserved) > 0');
    }

    /**
     * Scope a query to search stocks.
     */
    public function scopeSearch(Builder $query, string $search): Builder
    {
        return $query->where(function ($q) use ($search) {
            $q->where('sku', 'like', "%{$search}%")
                ->orWhere('barcode', 'like', "%{$search}%")
                ->orWhereHas('product', function ($productQuery) use ($search) {
                    $productQuery->where('name', 'like', "%{$search}%");
                });
        });
    }
}
