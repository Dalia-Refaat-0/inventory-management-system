<?php

namespace App\QueryFilters;

use Illuminate\Database\Eloquent\Builder;

final class Search extends Filter
{
    protected function filterName(): string { return 'search'; }

    protected function applyFilter(Builder $builder, mixed $value): Builder
    {
        return $builder->where(function (Builder $q) use ($value) {
        $q->whereFullText('name', $value)
            ->orWhere('sku', 'like', '%' . $value . '%');
        });
    }
}