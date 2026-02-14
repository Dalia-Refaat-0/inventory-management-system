<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Contracts\StockRepositoryInterface;
use App\DTOs\WarehouseInventoryData;
use App\Http\Resources\StockResource;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class WarehouseInventoryController extends Controller
{
    public function __construct(
        private readonly StockRepositoryInterface $repository,
    ) {}

    public function index(Request $request, string $id): AnonymousResourceCollection
    {
        $data = WarehouseInventoryData::fromRequest($request, (int) $id);
        
        return StockResource::collection(
            $this->repository->getWarehouseInventory($data)
        );
    }
}