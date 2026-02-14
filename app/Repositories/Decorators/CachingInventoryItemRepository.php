<?php

declare(strict_types=1);

namespace App\Repositories\Decorators;

use App\Contracts\InventoryItemRepositoryInterface;
use App\DTOs\InventoryFilterData;
use App\Services\Cache\CacheService;
use App\Services\Cache\CacheKeyGenerator;
use Illuminate\Contracts\Pagination\CursorPaginator;

final class CachingInventoryItemRepository implements InventoryItemRepositoryInterface
{
    public function __construct(
        private InventoryItemRepositoryInterface $inner,
        private CacheService $cache,
    ) {}

    public function getFiltered(InventoryFilterData $data): CursorPaginator
    {
        $ttl = (int) config('inventory.cache.cache_expirations.stock_inventory_seconds', 600);

        $key = CacheKeyGenerator::inventoryListKey(
            $data->toFilterArray(),
            $data->per_page,
            $data->cursor,
        );

        $tags = [
            CacheKeyGenerator::inventoryTag(),
            CacheKeyGenerator::warehousesTag(),
            CacheKeyGenerator::warehouseInventoryTag(),
        ];

        return $this->cache->rememberWithLock(
            $tags,
            $key,
            $ttl,
            fn () => $this->inner->getFiltered($data),
        );
    }
}