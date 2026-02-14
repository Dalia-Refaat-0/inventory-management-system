<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\InventoryItem
 */
class InventoryItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'    => $this->id,
            'name'  => $this->name,
            'sku'   => (string) $this->sku,
            'price' => (float) $this->price,
            'inventory' => WarehouseStockResource::collection($this->whenLoaded('stocks')),
            
            'total_stock' => $this->when($this->relationLoaded('stocks'), function() {
                return $this->stocks->sum('quantity');
            }),
            
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}