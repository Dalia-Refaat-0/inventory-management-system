<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\Warehouse
 */
class WarehouseResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'name'         => $this->name,
            'address'      => $this->address_text,
            'is_active'    => $this->is_active,
            'coordinates'  => $this->location ? [
                'latitude'  => $this->location->latitude,
                'longitude' => $this->location->longitude,
            ] : null,
            'stocks_count' => $this->whenCounted('stocks'),
            'updated_at'   => $this->updated_at->toIso8601String(),
        ];
    }
}