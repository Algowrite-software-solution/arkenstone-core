<?php

namespace Arkenstone\Core\ECommerce\Product\Events;

use Arkenstone\Core\ECommerce\Contracts\Product\ProductContract;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProductImagesUploaded
{
    use Dispatchable, SerializesModels;

    public ProductContract $product;
    public Collection $images;

    /**
     * Create a new event instance.
     */
    public function __construct(Collection $images)
    {
        $this->images = $images;

        Log::info("Product Images Uploaded Event: {$images->count()} images were added.", [
            'image_ids' => $images->pluck('id')->toArray()
        ]);
    }
}