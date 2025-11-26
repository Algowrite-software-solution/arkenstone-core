<?php

namespace Arkenstone\Core\ECommerce\Product\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductTaxonomy extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'taxonomy_id',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    /**
     * Get the product that owns the product taxonomy.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the taxonomy that owns the product taxonomy.
     */
    public function taxonomy(): BelongsTo
    {
        return $this->belongsTo(Taxonomy::class);
    }

}
