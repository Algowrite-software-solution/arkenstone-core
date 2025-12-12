<?php

namespace Arkenstone\Core\ECommerce\Stock\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Builder;

class VariationOption extends Model
{
    use HasFactory;

    protected $fillable = [
        'variant_id',
        'name',
        'meta',
    ];

    protected $casts = [
        'variant_id' => 'integer',
    ];

    /**
     * Get the variant that owns the variation option.
     */
    public function variant(): BelongsTo
    {
        return $this->belongsTo(Variant::class);
    }

    /**
     * Get the stocks that use this variation option.
     */
    public function stocks(): BelongsToMany
    {
        return $this->belongsToMany(Stock::class, 'stock_variant_options');
    }

    /**
     * Scope a query to filter by variant.
     */
    public function scopeByVariant(Builder $query, int $variantId): Builder
    {
        return $query->where('variant_id', $variantId);
    }
}
