<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Contracts\WarehouseRepositoryInterface;
use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Collection;

final class EloquentWarehouseRepository implements WarehouseRepositoryInterface
{
    public function getActive(bool $withCounts = false): Collection
    {
        $query = Warehouse::query()
            ->with(['location'])
            ->active();

        if ($withCounts) {
            $query->withCount('stocks');
        }

        return $query->orderBy('name')->get();
    }
}