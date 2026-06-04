<?php

namespace Arkenstone\Core\ECommerce\Product\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'image_url' => $this->image_url ? asset('storage/' . ltrim($this->image_url, '/')) : null,
            'alt_text' => $this->alt_text,
            'description' => $this->description,
            'parent_id' => $this->parent_id,
            'is_active' => $this->is_active,
            'parent' => new CategoryResource($this->whenLoaded('parent')),
            'taxonomies' => TaxonomyResource::collection($this->whenLoaded('taxonomies')),
            'children' => CategoryResource::collection($this->whenLoaded('children')),
            'products_count' => $this->when($this->relationLoaded('products'), fn() => $this->products->count()),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
