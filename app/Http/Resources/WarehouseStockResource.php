<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\Stock
 */
class WarehouseStockResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'warehouse_id'   => $this->warehouse->id,
            'warehouse_name' => $this->warehouse->name,
            'quantity'       => $this->quantity,
            'status'         => $this->quantity <= $this->low_stock_threshold ? 'low_stock' : 'in_stock',
        ];
    }
}