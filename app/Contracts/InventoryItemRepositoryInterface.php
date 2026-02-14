<?php

declare(strict_types=1);

namespace App\Contracts;

use App\DTOs\InventoryFilterData;
use Illuminate\Contracts\Pagination\CursorPaginator;

interface InventoryItemRepositoryInterface
{
    public function getFiltered(InventoryFilterData $data): CursorPaginator;
}