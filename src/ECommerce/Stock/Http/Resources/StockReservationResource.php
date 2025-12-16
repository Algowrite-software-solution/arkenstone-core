<?php

namespace Arkenstone\Core\ECommerce\Stock\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StockReservationResource extends JsonResource
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
            'stock_id' => $this->stock_id,
            'quantity' => $this->quantity,
            'status' => $this->status,
            'reference_type' => $this->reference_type,
            'reference_id' => $this->reference_id,
            'expires_at' => $this->expires_at?->toISOString(),
            'notes' => $this->notes,
            'is_expired' => $this->isExpired(),
            'is_pending' => $this->isPending(),
            'is_committed' => $this->isCommitted(),
            'stock' => new StockResource($this->whenLoaded('stock')),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
