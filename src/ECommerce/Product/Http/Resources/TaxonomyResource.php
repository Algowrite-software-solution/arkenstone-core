<?php

namespace Arkenstone\Core\ECommerce\Product\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaxonomyResource extends JsonResource
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
            'taxonomy_type_id' => $this->taxonomy_type_id,
            'parent_id' => $this->parent_id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'sort_order' => $this->sort_order,
            'meta' => $this->meta,
            'is_active' => $this->is_active,
            'taxonomy_type' => $this->whenLoaded('taxonomyType'),
            'parent' => new TaxonomyResource($this->whenLoaded('parent')),
            'children' => TaxonomyResource::collection($this->whenLoaded('children')),
            'products_count' => $this->when($this->relationLoaded('products'), fn() => $this->products->count()),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
