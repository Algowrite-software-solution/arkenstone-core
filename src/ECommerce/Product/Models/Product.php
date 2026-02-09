<?php

namespace Arkenstone\Core\ECommerce\Product\Models;


use Arkenstone\Core\Database\Factories\ProductFactory;
use Arkenstone\Core\ECommerce\Contracts\Product\ProductContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Product extends Model implements ProductContract
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'minified_description',
        'details',
        'price',
        'sku',
        'quantity',
        'discount_type',
        'discount_value',
        'brand_id',
        'is_active',
        'bundle_id',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_active' => 'boolean',
        'discount_value' => 'decimal:2',
        'discount_type' => \Arkenstone\Core\ECommerce\Product\Enum\DiscountType::class,
        'details' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($product) {
            if (empty($product->slug)) {
                $product->slug = \Illuminate\Support\Str::slug($product->name);
            }
        });
    }

    /**
     * Get the brand that owns the product.
     */
    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    /**
     * Get the categories that belong to the product.
     */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'product_categories');
    }


    /**
     * Get the images for the product.
     */
    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class);
    }

    /**
     * |-----------------------------------------------------------------------------|
     * |  Get the primary image for the product.                                     |
     * |-----------------------------------------------------------------------------|
     */
    public function primaryImage(): HasOne
    {
        return $this->hasOne(ProductImage::class)->where('is_primary', true)->orderBy('id');
    }

    public function taxonomies(): BelongsToMany
    {
        return $this->belongsToMany(Taxonomy::class, 'product_taxonomies')->withTimestamps();
    }

    public function productTaxonomies()
    {
        return $this->hasMany(ProductTaxonomy::class);
    }


    /**
     * |-----------------------------------------------------------------------------|
     * |  Scopes for the product model                                               | 
     * |-----------------------------------------------------------------------------|
     */
    public function scopeIsActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }


    public function scopeFilterByName(Builder $query, string $name): Builder
    {
        Log::info("Filtering by name", ['name' => $name]);
        return $query->where('name', 'like', '%' . $name . '%');
    }


    public function scopeByIds(Builder $query, array $ids): Builder
    {
        return $query->whereIn('id', $ids);
    }
    public function scopeMinPrice(Builder $query, float $price): Builder
    {
        return $query->where('price', '>=', $price);
    }

    public function scopeMaxPrice(Builder $query, float $price): Builder
    {
        return $query->where('price', '<=', $price);
    }

    public function scopeByCategory(Builder $query, int $id): Builder
    {
        return $query;
    }

    public function scopeByCategories(Builder $query, array $ids): Builder
    {
        return $query->whereHas('categories', function (Builder $q) use ($ids) {
            $q->whereIn('categories.id', $ids);
        });
    }

    public function scopeByTaxonomies(Builder $query, array $ids): Builder
    {
        return $query->whereHas('taxonomies', function (Builder $q) use ($ids) {
            $q->whereIn('taxonomies.id', $ids);
        });
    }

    public function scopeByAllCategories(Builder $query, array $ids): Builder
    {
        foreach ($ids as $id) {
            $query->whereHas('categories', fn($q) => $q->where('categories.id', $id));
        }
        return $query;
    }

    public function scopeByBrand(Builder $query, int $id): Builder
    {
        return $query->where('brand_id', $id);
    }

    public function scopeByBrands(Builder $query, array $ids): Builder
    {
        return $query->whereIn('brand_id', $ids);
    }


    /**
     * |-----------------------------------------------------------------------------|
     * |  Discount system methods                                                    |
     * |-----------------------------------------------------------------------------|
     */

    /**
     * Check if product has an active discount.
     */
    public function hasDiscount(): bool
    {
        return $this->discount_type !== null && $this->discount_value > 0;
    }

    /**
     * Calculate and return the sale price based on discount type.
     */
    public function getSalePriceAttribute(): ?float
    {
        if (!$this->hasDiscount()) {
            return null;
        }

        return match ($this->discount_type) {
            \Arkenstone\Core\ECommerce\Product\Enum\DiscountType::PERCENTAGE =>
            $this->price - ($this->price * ($this->discount_value / 100)),
            \Arkenstone\Core\ECommerce\Product\Enum\DiscountType::FIXED_AMOUNT =>
            max(0, $this->price - $this->discount_value),
            default => null,
        };
    }

    /**
     * Stock related methods
     */
    public function stocks(): HasMany
    {
        return $this->hasMany(\Arkenstone\Core\ECommerce\Stock\Models\Stock::class);
    }

    /**
     * Bundle relationships
     */
    public function bundle(): BelongsTo
    {
        return $this->belongsTo(Bundle::class);
    }

    public function isBundle(): bool
    {
        return !is_null($this->bundle_id);
    }

    /**
     * Create a new factory instance for the model.
     */

    protected static function newFactory(): Factory
    {
        return ProductFactory::new();
    }
}