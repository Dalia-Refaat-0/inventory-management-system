<?php

declare(strict_types=1);

namespace App\Repositories\Decorators;

use App\Contracts\StockRepositoryInterface;
use App\DTOs\WarehouseInventoryData;
use App\Models\Stock;
use App\Services\Cache\CacheKeyGenerator;
use App\Services\Cache\CacheService;
use Illuminate\Contracts\Pagination\CursorPaginator;

final readonly class CachingStockRepository implements StockRepositoryInterface
{
    public function __construct(
        private StockRepositoryInterface $inner,
        private CacheService $cache,
    ) {}

    public function getWarehouseInventory(WarehouseInventoryData $data): CursorPaginator
    {
        $ttl = (int) config('inventory.cache.cache_expirations.warehouse_inventory_seconds', 600);

        $key = CacheKeyGenerator::warehouseInventoryKey(
            $data->warehouseId,
            $data->perPage,
            $data->cursor,
        );

        $tags = [
            CacheKeyGenerator::warehouseTag($data->warehouseId),
        ];

        return $this->cache->rememberWithLock(
            $tags,
            $key,
            $ttl,
            fn () => $this->inner->getWarehouseInventory($data),
        );
    }

    public function findForWarehouseItem(int $warehouseId, int $itemId, bool $lock = false): ?Stock
    {
        return $this->inner->findForWarehouseItem($warehouseId, $itemId, $lock);
    }

    public function firstOrCreateForWarehouseItem(int $warehouseId, int $itemId): Stock
    {
        $stock = $this->inner->firstOrCreateForWarehouseItem($warehouseId, $itemId);

        if ($stock->wasRecentlyCreated) {
            $this->invalidateWarehouseCache($warehouseId);
        }

        return $stock;
    }

    public function decrementQuantity(Stock $stock, int $amount): void
    {
        $warehouseId = $stock->warehouse_id;

        $this->inner->decrementQuantity($stock, $amount);

        $this->invalidateWarehouseCache($warehouseId);
    }

    public function deleteIfEmpty(Stock $stock): bool
    {
        $warehouseId = $stock->warehouse_id;

        $deleted = $this->inner->deleteIfEmpty($stock);

        if ($deleted) {
            $this->invalidateWarehouseCache($warehouseId);
        }

        return $deleted;
    }

    public function incrementQuantity(Stock $stock, int $amount): void
    {
        $warehouseId = $stock->warehouse_id;

        $this->inner->incrementQuantity($stock, $amount);

        $this->invalidateWarehouseCache($warehouseId);
    }

    private function invalidateWarehouseCache(int $warehouseId): void
    {
        $this->cache->flush([
            CacheKeyGenerator::warehouseTag($warehouseId),
        ]);
    }
}