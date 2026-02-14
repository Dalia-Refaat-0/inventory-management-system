<?php

declare(strict_types=1);

namespace App\QueryFilters;

use Illuminate\Database\Eloquent\Builder;

final class WarehouseFilter extends Filter
{
    protected function filterName(): string { return 'warehouse_id'; }

    protected function applyFilter(Builder $builder, mixed $value): Builder
    {
        $ids = (array) $value;

        return $builder->whereHas('stocks', function (Builder $query) use ($ids) {
            $query->whereIn('warehouse_id', $ids);
        });
    }
}