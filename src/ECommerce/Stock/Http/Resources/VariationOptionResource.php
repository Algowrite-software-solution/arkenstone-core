<?php

namespace Arkenstone\Core\ECommerce\Stock\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VariationOptionResource extends JsonResource
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
            'variant_id' => $this->variant_id,
            'name' => $this->name,
            'meta' => $this->meta,
            'variant' => new VariantResource($this->whenLoaded('variant')),
            'stocks_count' => $this->when($this->relationLoaded('stocks'), fn() => $this->stocks->count()),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
