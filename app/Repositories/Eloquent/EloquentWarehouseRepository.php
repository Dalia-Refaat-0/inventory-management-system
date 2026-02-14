<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Contracts\WarehouseRepositoryInterface;
use App\Models\Warehouse;
use Illuminate\Contracts\Pagination\CursorPaginator;

final class EloquentWarehouseRepository implements WarehouseRepositoryInterface
{
    public function getActive(int $perPage = 15, bool $withCounts = false): CursorPaginator
    {
        $query = Warehouse::query()
            ->active();

        if ($withCounts) {
            $query->withCount('stocks');
        }

        return $query->orderBy('name')->cursorPaginate($perPage);
    }
}