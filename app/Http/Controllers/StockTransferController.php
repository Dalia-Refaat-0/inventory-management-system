<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\DTOs\TransferStockData;
use App\Http\Resources\StockTransferResource;
use App\Services\StockTransferService;
use Illuminate\Http\JsonResponse;

final class StockTransferController extends Controller
{
    public function __construct(
        private readonly StockTransferService $service
    ) {}

    public function store(TransferStockData $data): JsonResponse
    {
        $user = auth()->user();

        $transfer = $this->service->transfer($data, $user);

        return (new StockTransferResource($transfer))
            ->response()
            ->setStatusCode(201);
    }
}