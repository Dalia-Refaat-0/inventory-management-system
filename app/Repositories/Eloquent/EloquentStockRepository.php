<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Contracts\StockRepositoryInterface;
use App\DTOs\WarehouseInventoryData;
use App\Models\Stock;
use Illuminate\Contracts\Pagination\CursorPaginator;
use InvalidArgumentException;

final class EloquentStockRepository implements StockRepositoryInterface
{
    public function getWarehouseInventory(WarehouseInventoryData $data): CursorPaginator
    {
        return Stock::where('warehouse_id', $data->warehouseId)
            ->with(['inventoryItem'])
            ->orderBy('id', 'desc')
            ->cursorPaginate($data->perPage);
    }

    public function findForWarehouseItem(int $warehouseId, int $itemId, bool $lock = false): ?Stock
    {
        $query = Stock::where('warehouse_id', $warehouseId)
            ->where('inventory_item_id', $itemId);

        if ($lock) {
            $query->lockForUpdate();
        }

        return $query->first();
    }

    public function firstOrCreateForWarehouseItem(int $warehouseId, int $itemId): Stock
    {
        return Stock::firstOrCreate(
            [
                'warehouse_id' => $warehouseId,
                'inventory_item_id' => $itemId,
            ],
            [
                'quantity' => 0,
            ]
        );
    }

    public function decrementQuantity(Stock $stock, int $amount): void
    {
        $this->validateAmount($amount);
        $stock->decrement('quantity', $amount);
    }

    public function incrementQuantity(Stock $stock, int $amount): void
    {
        $this->validateAmount($amount);

        $stock->increment('quantity', $amount);
    }

    public function deleteIfEmpty(Stock $stock): bool
    {
        $stock->refresh();

        if ($stock->quantity <= 0) {
            $stock->delete();
            return true;
        }

        return false;
    }

    private function validateAmount(int $amount): void
    {
        if ($amount <= 0) {
            throw new InvalidArgumentException('Amount must be a positive integer');
        }
    }
}