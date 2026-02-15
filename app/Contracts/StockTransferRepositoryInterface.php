<?php

declare(strict_types=1);

namespace App\Contracts;

use App\DTOs\StockTransferCreationData;
use App\Models\StockTransfer;

interface StockTransferRepositoryInterface
{
    public function create(StockTransferCreationData $data, array $relations = []): StockTransfer;
    public function findOrFail(int $id): StockTransfer;

    public function referenceExists(string $reference): bool;
}