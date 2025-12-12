<?php

namespace Arkenstone\Core\ECommerce\Stock\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class Variant extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
    ];

    /**
     * Get the variation options for the variant.
     */
    public function variationOptions(): HasMany
    {
        return $this->hasMany(VariationOption::class);
    }

    /**
     * Scope a query to search variants.
     */
    public function scopeSearch(Builder $query, string $search): Builder
    {
        return $query->where('name', 'like', "%{$search}%");
    }
}
