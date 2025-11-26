<?php

namespace Arkenstone\Core\ECommerce\Product\Events;

use Arkenstone\Core\ECommerce\Contracts\Product\ProductContract;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProductDeleted
{
    use Dispatchable, SerializesModels;

    public ProductContract $product;

    /**
     * Create a new event instance.
     */
    public function __construct(ProductContract $product)
    {
        $this->product = $product;
        Log::info("Product Deleted Event: A product was soft-deleted.", ['id' => $product->id, 'name' => $product->name]);
    }
}