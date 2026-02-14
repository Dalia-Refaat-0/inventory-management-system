<?php

namespace App\QueryFilters;

use Illuminate\Database\Eloquent\Builder;

class Search extends Filter
{
    protected function filterName(): string { return 'search'; }

    protected function applyFilter(Builder $builder, mixed $value): Builder
    {
        return $builder->where(function (Builder $q) use ($value) {
            $q->where('name', 'like', '%' . $value . '%')
            ->orWhere('sku', 'like', '%' . $value . '%');
        });
    }
}