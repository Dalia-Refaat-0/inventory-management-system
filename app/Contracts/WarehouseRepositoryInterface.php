<?php

declare(strict_types=1);

namespace App\Contracts;

use Illuminate\Contracts\Pagination\CursorPaginator;

interface WarehouseRepositoryInterface
{
    public function getActive(int $perPage = 15, bool $withCounts = false): CursorPaginator;

}