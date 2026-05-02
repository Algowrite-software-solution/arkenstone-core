<?php

namespace Arkenstone\Core\ECommerce\Product\Models;

use Arkenstone\Core\Database\Factories\TaxonomyTypeFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\Factory;

class TaxonomyType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the taxonomies for the taxonomy type.
     */
    public function taxonomies(): HasMany
    {
        return $this->hasMany(Taxonomy::class);
    }

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): Factory
    {
        return TaxonomyTypeFactory::new();
    }
}
