<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\Stock
 */
class StockResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'item_id'   => $this->inventory_item_id,
            'name'      => $this->inventoryItem->name,
            'sku'       => (string) $this->inventoryItem->sku,
            'quantity'  => $this->quantity,
            'status'    => [
                'is_low'    => $this->isLowStock(),
                'threshold' => $this->low_stock_threshold,
            ],
            'last_updated' => $this->updated_at->toIso8601String(),
        ];
    }
}