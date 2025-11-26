<?php

namespace Arkenstone\Core\ECommerce\Product\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductTaxonomyResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'product_id' => $this->product_id,
            'taxonomy_id' => $this->taxonomy_id,
            'product' => new ProductResource($this->whenLoaded('product')),
            'taxonomy' => new TaxonomyResource($this->whenLoaded('taxonomy')),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
