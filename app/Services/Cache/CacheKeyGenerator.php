<?php

declare(strict_types=1);

namespace App\Services\Cache;

final class CacheKeyGenerator
{
    public static function activeWarehousesKey(
        bool $withCounts,
        int $perPage = 15,
        string $cursor = 'first'
    ): string {
        $suffix = $withCounts ? 'with_counts' : 'standard';

        return "warehouses:active_list:{$suffix}:pp{$perPage}:c_{$cursor}";
    }

    public static function warehouseInventory(int $warehouseId): string
    {
        return "warehouse:{$warehouseId}:inventory";
    }

    public static function warehouseTag(int $warehouseId): string
    {
        return "warehouse:{$warehouseId}";
    }

    public static function warehousesTag(): string
    {
        return 'warehouses';
    }

    public static function warehouseInventoryTag(): string
    {
        return 'warehouse-inventory';
    }

    public static function inventoryListKey(array $filters, int $perPage, ?string $cursor): string
    {
        ksort($filters);
        $filterHash = md5(json_encode($filters));
        
        return "inventory:list:{$filterHash}:pp{$perPage}:c_{$cursor}";
    }

    public static function inventoryTag(): string
    {
        return 'inventory_items';
    }
}