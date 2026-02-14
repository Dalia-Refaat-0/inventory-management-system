<?php

declare(strict_types=1);

namespace App\Contracts;

use App\DTOs\WarehouseInventoryData;
use App\Models\Stock;
use Illuminate\Contracts\Pagination\CursorPaginator;

interface StockRepositoryInterface
{
    public function getWarehouseInventory(WarehouseInventoryData $data): CursorPaginator;

    public function findForWarehouseItem(int $warehouseId, int $itemId, bool $lock = false): ?Stock;

    public function firstOrCreateForWarehouseItem(int $warehouseId, int $itemId): Stock;

    public function decrementQuantity(Stock $stock, int $amount): void;

    public function deleteIfEmpty(Stock $stock): bool;
    public function incrementQuantity(Stock $stock, int $amount): void;
}