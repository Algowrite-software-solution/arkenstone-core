<?php

namespace Arkenstone\Core\ECommerce\Product\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class CategoryTaxonomy extends Pivot
{
    protected $table = 'category_taxonomy';

    protected $fillable = [
        'category_id',
        'taxonomy_id',
    ];
}