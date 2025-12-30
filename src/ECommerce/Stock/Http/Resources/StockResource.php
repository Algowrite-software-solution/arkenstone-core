<?php

namespace Arkenstone\Core\ECommerce\Stock\Http\Resources;

use Arkenstone\Core\ECommerce\Product\Http\Resources\ProductResource;
use Arkenstone\Core\ECommerce\Product\Http\Resources\ProductImageResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StockResource extends JsonResource
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
            'product_id' => $this->product_id,
            'sku' => $this->sku,
            'barcode' => $this->barcode,
            'price' => $this->price,
            'cost' => $this->cost,
            'weight' => $this->weight,
            'quantity_on_hand' => $this->quantity_on_hand,
            'quantity_reserved' => $this->quantity_reserved,
            'quantity_available' => $this->quantity_available,
            'min_stock_level' => $this->min_stock_level,
            'supplier_id' => $this->supplier_id,
            'image_url_id' => $this->image_url_id,
            'status' => $this->status,
            'is_available' => $this->isAvailable(),
            'is_low_stock' => $this->isLowStock(),
            'product' => new ProductResource($this->whenLoaded('product')),
            'supplier' => new SupplierResource($this->whenLoaded('supplier')),
            'variation_options' => VariationOptionResource::collection($this->whenLoaded('variationOptions')),
            'image' => new ProductImageResource($this->whenLoaded('image')),
            'reservations' => StockReservationResource::collection($this->whenLoaded('reservations')),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
