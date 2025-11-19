<?php
namespace Arkenstone\Core\ECommerce\Product\Http\Resources;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;


class TaxonomyTypeResource extends JsonResource
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
            'description' => $this->description,
            'is_active' => $this->is_active,
            'taxonomies_count' => $this->when($this->relationLoaded('taxonomies'), fn() => $this->taxonomies->count()),
            'taxonomies' => $this->when($this->relationLoaded('taxonomies'), fn() => TaxonomyResource::collection($this->taxonomies)),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}