<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Contracts\InventoryItemRepositoryInterface;
use App\DTOs\InventoryFilterData;
use App\Models\InventoryItem;
use App\QueryFilters\Search;
use App\QueryFilters\MaxPrice;
use App\QueryFilters\MinPrice;
use App\QueryFilters\WarehouseFilter;
use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Pipeline\Pipeline;

final class EloquentInventoryItemRepository implements InventoryItemRepositoryInterface
{
    public function __construct(
        private readonly Pipeline $pipeline,
    ) {}

    public function getFiltered(InventoryFilterData $data): CursorPaginator
    {
        $context = $this->pipeline
            ->send([
                'builder' => InventoryItem::query(),
                'filters' => $data->toFilterArray(),
            ])
            ->through([
                Search::class,
                MinPrice::class,
                MaxPrice::class,
                WarehouseFilter::class,
            ])
            ->thenReturn();

        return $context['builder']
            ->with(['stocks.warehouse'])
            ->orderBy('id', 'desc')
            ->cursorPaginate($data->per_page);
    }
}