<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Contracts\StockTransferRepositoryInterface;
use App\DTOs\StockTransferCreationData;
use App\Models\StockTransfer;

final class EloquentStockTransferRepository implements StockTransferRepositoryInterface
{
    public function create(StockTransferCreationData $data, array $relations = []): StockTransfer
    {
        $transfer = StockTransfer::create($data->toArray());
        return $transfer->load($relations);
    }

    public function findOrFail(int $id): StockTransfer
    {
        return StockTransfer::query()
            ->with(['sourceWarehouse', 'destinationWarehouse', 'inventoryItem'])
            ->findOrFail($id);
    }

    public function referenceExists(string $reference): bool
    {
        return StockTransfer::query()
            ->where('reference_number', $reference)
            ->exists();
    }
}