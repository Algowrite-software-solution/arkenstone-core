<?php

namespace Arkenstone\Core\ECommerce\Stock\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VariantResource extends JsonResource
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
            'variation_options' => VariationOptionResource::collection($this->whenLoaded('variationOptions')),
            'options_count' => $this->when($this->relationLoaded('variationOptions'), fn() => $this->variationOptions->count()),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
