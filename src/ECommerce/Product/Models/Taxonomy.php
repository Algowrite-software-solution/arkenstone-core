<?php

namespace Arkenstone\Core\ECommerce\Product\Models;

use Arkenstone\Core\Database\Factories\TaxonomyFactory;
use Arkenstone\Core\ECommerce\Product\Scopes\ActiveScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\Factory;

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

    protected static function booted()
    {
        static::addGlobalScope(new ActiveScope());
    }

    /**
     * Get the taxonomy type that owns the taxonomy.
     */
    public function taxonomyType(): BelongsTo
    {
        return $this->belongsTo(TaxonomyType::class);
    }

    /**
     * Alias for taxonomyType() - for backward compatibility
     */
    public function type(): BelongsTo
    {
        return $this->taxonomyType();
    }

    /**
     * Get the cateogires belong to this taxonomy
     */
    public function categories()
    {
        return $this->belongsToMany(Category::class)
            ->using(CategoryTaxonomy::class)
            ->withTimestamps();
    }

    /**
     * Get the parent taxonomy.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Taxonomy::class , 'parent_id');
    }

    /**
     * Get the child taxonomies.
     */
    public function children(): HasMany
    {
        return $this->hasMany(Taxonomy::class , 'parent_id');
    }

    /**
     * Get the products that belong to the taxonomy.
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class , 'product_taxonomies')->withTimestamps();
    }

    // Query scopes
    public function scopeByType($query, int $typeId)
    {
        return $query->where('taxonomy_type_id', $typeId);
    }

    public function scopeIsActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeFilterByName($query, string $name)
    {
        return $query->where('name', 'like', '%' . $name . '%');
    }

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): Factory
    {
        return TaxonomyFactory::new ();
    }
}