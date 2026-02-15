<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\{StockRepositoryInterface, StockTransferRepositoryInterface};
use App\DTOs\{StockTransferCreationData, TransferStockData};
use App\Enums\TransferStatus;
use App\Events\LowStockDetected;
use App\Exceptions\InsufficientStockException;
use App\Models\{Stock, StockTransfer, User};
use App\Support\TransferReferenceGenerator;
use Illuminate\Support\Facades\DB;

final class StockTransferService
{
    public function __construct(
        private readonly StockRepositoryInterface $stockRepo,
        private readonly StockTransferRepositoryInterface $transferRepo,
        private readonly TransferReferenceGenerator $referenceGenerator,
    ) {}

    public function transfer(TransferStockData $data, User $user): StockTransfer
    {
        [$transfer, $sourceStock] = DB::transaction(fn() => $this->executeTransfer($data, $user));

        $this->notifyIfStockIsLow($sourceStock);

        return $transfer;
    }

    private function executeTransfer(TransferStockData $data, User $user): array
    {
        $source = $this->getValidSourceStock($data);

        $this->performInventoryMovement($data, $source);

        $transfer = $this->transferRepo->create(
            StockTransferCreationData::fromTransfer(
                data: $data,
                status: TransferStatus::Completed,
                referenceNumber: $this->referenceGenerator->generate(),
                transferredBy: $user->id
            ),['sourceWarehouse', 'destinationWarehouse', 'inventoryItem']
        );

        return [$transfer, $source->refresh()];
    }

    private function getValidSourceStock(TransferStockData $data): Stock
    {
        $stock = $this->stockRepo->findForWarehouseItem($data->source_warehouse_id, $data->inventory_item_id, lock: true);

        if (!$stock || $stock->quantity < $data->quantity) {
            throw new InsufficientStockException($data->quantity, $stock->quantity ?? 0);
        }

        return $stock;
    }

    private function performInventoryMovement(TransferStockData $data, Stock $source): void
    {
        $this->stockRepo->decrementQuantity($source, $data->quantity);
        $this->stockRepo->deleteIfEmpty($source,);

        $destination = $this->stockRepo->firstOrCreateForWarehouseItem(
            $data->destination_warehouse_id,
            $data->inventory_item_id
        );

        $this->stockRepo->incrementQuantity($destination, $data->quantity);
    }

    private function notifyIfStockIsLow(Stock $stock): void
    {
        if ($stock->isLowStock()) {
            LowStockDetected::dispatchFor($stock);
        }
    }
}