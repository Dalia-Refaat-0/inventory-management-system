<?php

declare(strict_types=1);

namespace App\QueryFilters;

use Illuminate\Database\Eloquent\Builder;

final class MinPrice extends Filter
{
    /**
     * The name of the query parameter.
     */
    protected function filterName(): string 
    { 
        return 'min_price'; 
    }

    /**
     * Apply the filter logic to the query builder.
     */
    protected function applyFilter(Builder $builder, mixed $value): Builder
    {
        return $builder->where('price', '>=', $value);
    }

}