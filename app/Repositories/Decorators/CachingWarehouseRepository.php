<?php

declare(strict_types=1);

namespace App\Repositories\Decorators;

use App\Contracts\WarehouseRepositoryInterface;
use App\Services\Cache\CacheService;
use App\Services\Cache\CacheKeyGenerator;
use Illuminate\Contracts\Pagination\CursorPaginator;

final class CachingWarehouseRepository implements WarehouseRepositoryInterface
{
    public function __construct(
        private WarehouseRepositoryInterface $inner,
        private CacheService $cache
    ) {}

    public function getActive(int $perPage = 15, bool $withCounts = false): CursorPaginator
    {
        $expirationInSeconds = (int) config('inventory.cache.cache_expirations.warehouse_expiration_seconds', 3600);

        $cursor = request()->input('cursor', 'first');
        
        $key  = CacheKeyGenerator::activeWarehousesKey($withCounts, $perPage, (string) $cursor);
        $tags = [CacheKeyGenerator::warehousesTag()];

        return $this->cache->rememberWithLock(
            $tags,
            $key,
            $expirationInSeconds,
            fn() => $this->inner->getActive($perPage, $withCounts)
        );
    }
}