<?php

namespace Arkenstone\Core\ECommerce\Product\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductImageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $imageUrl = $this->image_url;
        if ($imageUrl && !filter_var($imageUrl, FILTER_VALIDATE_URL)) {
            // Return relative path with 'storage/' prefix
            $imageUrl = 'storage/' . ltrim($imageUrl, '/');
        }

        return [
            'id' => $this->id,
            'product_id' => $this->product_id,
            'image_url' => $imageUrl,
            'alt_text' => $this->alt_text,
            'is_primary' => $this->is_primary,
            'sort_order' => $this->sort_order,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
