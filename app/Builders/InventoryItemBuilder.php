<?php

declare(strict_types=1);

namespace App\Builders;

use Illuminate\Database\Eloquent\Builder;

/**
 * @template TModel of \App\Models\InventoryItem
 *
 * @extends Builder<TModel>
 */
class InventoryItemBuilder extends Builder
{
    public function inWarehouse(int $warehouseId): self
    {
        return $this->whereHas(
            'stocks',
            fn (Builder $q) => $q->where('warehouse_id', $warehouseId)
        );
    }
}