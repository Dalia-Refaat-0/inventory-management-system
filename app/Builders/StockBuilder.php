<?php

declare(strict_types=1);

namespace App\Builders;

use Illuminate\Database\Eloquent\Builder;

/**
 * @template TModel of \App\Models\Stock
 * @extends Builder<TModel>
 */
class StockBuilder extends Builder
{
    public function forWarehouseItem(int $warehouseId, int $itemId): self
    {
        return $this
            ->where('warehouse_id', $warehouseId)
            ->where('inventory_item_id', $itemId);
    }

    public function lowStock(): self
    {
        return $this->whereColumn('quantity', '<=', 'low_stock_threshold');
    }
}