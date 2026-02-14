<?php

declare(strict_types=1);

namespace App\QueryFilters;

use Closure;
use Illuminate\Database\Eloquent\Builder;

abstract class Filter
{
    public function handle(array $context, Closure $next): mixed
    {
        $value = $context['filters'][$this->filterName()] ?? null;

        if ($value !== null && $value !== '') {
            $context['builder'] = $this->applyFilter($context['builder'], $value);
        }

        return $next($context);
    }

    abstract protected function filterName(): string;
    abstract protected function applyFilter(Builder $builder, mixed $value): Builder;
}