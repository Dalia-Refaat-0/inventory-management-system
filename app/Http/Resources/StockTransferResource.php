<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class StockTransferResource extends JsonResource
{

    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'reference_number' => $this->reference_number,
            'status'           => $this->status->value,
            'quantity'         => $this->quantity,
            
            'item' => new InventoryItemResource(
                $this->whenLoaded('inventoryItem')
            ),
            
            'source' => new WarehouseResource(
                $this->whenLoaded('sourceWarehouse')
            ),
            
            'destination' => new WarehouseResource(
                $this->whenLoaded('destinationWarehouse')
            ),

            'notes' => $this->whenNotNull($this->notes),

            'transferred_by' => $this->when($this->relationLoaded('transferredByUser'), function () {
                return [
                    'id'   => $this->transferredByUser->id,
                    'name' => $this->transferredByUser->name,
                ];
            }),

            'dates' => [
                'created_at' => $this->created_at->toIso8601String(),
                'updated_at' => $this->updated_at->toIso8601String(),
            ],
        ];
    }
}