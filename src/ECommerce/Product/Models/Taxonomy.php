<?php

namespace Arkenstone\Core\ECommerce\Product\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Taxonomy extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'taxonomy_type_id',
        'parent_id',
        'name',
        'slug',
        'description',
        'sort_order',
        'meta',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'meta' => 'array',
    ];

    /**
     * Get the taxonomy type that owns the taxonomy.
     */
    public function taxonomyType(): BelongsTo
    {
        return $this->belongsTo(TaxonomyType::class);
    }

    /**
     * Get the parent taxonomy.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Taxonomy::class, 'parent_id');
    }

    /**
     * Get the child taxonomies.
     */
    public function children(): HasMany
    {
        return $this->hasMany(Taxonomy::class, 'parent_id');
    }

    /**
     * Get the products that belong to the taxonomy.
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_taxonomies')->withTimestamps();
    }
}
