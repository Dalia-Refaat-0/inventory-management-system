<?php

declare(strict_types=1);

namespace App\Services\Cache;

final class CacheKeyGenerator
{
    private const CURSOR_DEFAULT = 'first';

    public static function activeWarehousesKey(
        bool $withCounts,
        int $perPage = 15,
        ?string $cursor = null
    ): string {
        $suffix = $withCounts ? 'with_counts' : 'standard';
        $cursorValue = self::normalizeCursor($cursor);

        return "warehouses:active_list:{$suffix}:pp{$perPage}:c_{$cursorValue}";
    }

    public static function warehouseTag(int $warehouseId): string
    {
        return "tag:warehouse:{$warehouseId}";
    }

    public static function warehousesTag(): string
    {
        return 'tag:warehouses';
    }

    public static function warehouseInventoryTag(): string
    {
        return 'tag:warehouse_inventory';
    }

    public static function inventoryTag(): string
    {
        return 'tag:inventory';
    }

    public static function inventoryListKey(
        array $filters,
        int $perPage,
        ?string $cursor = null
    ): string {
        ksort($filters);
        $json = json_encode($filters, JSON_THROW_ON_ERROR);
        $filterHash = md5($json);
        $cursorValue = self::normalizeCursor($cursor);

        return "inventory:list:{$filterHash}:pp{$perPage}:c_{$cursorValue}";
    }


    public static function warehouseInventoryKey(
        int $warehouseId,
        int $perPage,
        ?string $cursor = null
    ): string {
        $cursorValue = self::normalizeCursor($cursor);

        return "warehouse:{$warehouseId}:inventory:pp{$perPage}:c_{$cursorValue}";
    }

    private static function normalizeCursor(?string $cursor): string
    {
        if ($cursor === null || trim($cursor) === '') {
            return self::CURSOR_DEFAULT;
        }

        if (strlen($cursor) > 50) {
            return 'h_' . substr(md5($cursor), 0, 16);
        }

        return $cursor;
    }
}