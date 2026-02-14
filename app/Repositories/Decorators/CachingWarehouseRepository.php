<?php

declare(strict_types=1);

namespace App\Repositories\Decorators;

use App\Contracts\WarehouseRepositoryInterface;
use App\Services\Cache\CacheService;
use App\Services\Cache\CacheKeyGenerator;
use Illuminate\Database\Eloquent\Collection;

final class CachingWarehouseRepository implements WarehouseRepositoryInterface
{
    public function __construct(
        private WarehouseRepositoryInterface $inner,
        private CacheService $cache
    ) {}

    public function getActive(bool $withCounts = false): Collection
    {
        $expirationInSeconds = config('inventory.cache.cache_expirations.warehouse_expiration_seconds');
        
        $key  = CacheKeyGenerator::activeWarehousesKey($withCounts);
        $tags = [CacheKeyGenerator::warehousesTag()];

        return $this->cache->rememberWithLock(
            $tags, 
            $key, 
            $expirationInSeconds, 
            fn() => $this->inner->getActive($withCounts)
        );
    }
}