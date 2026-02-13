<?php

declare(strict_types=1);

namespace App\Builders;

use Illuminate\Database\Eloquent\Builder;

/**
 * @template TModel of \App\Models\Warehouse
 * @extends Builder<TModel>
 */
class WarehouseBuilder extends Builder
{
    public function active(): self
    {
        return $this->where('is_active', true);
    }
}