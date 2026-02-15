<?php

namespace App\QueryFilters;

use Illuminate\Database\Eloquent\Builder;

final class MaxPrice extends Filter
{
    protected function filterName(): string { return 'max_price'; }

    protected function applyFilter(Builder $builder, mixed $value): Builder
    {
        return $builder->where('price', '<=', $value);
    }
}