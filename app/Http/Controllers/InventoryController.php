<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Contracts\InventoryItemRepositoryInterface;
use App\DTOs\InventoryFilterData;
use App\Http\Controllers\Controller;
use App\Http\Resources\InventoryItemResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class InventoryController extends Controller
{
    public function __construct(
        private readonly InventoryItemRepositoryInterface $repository,
    ) {}

    public function index(InventoryFilterData $data): AnonymousResourceCollection
    {
        $data = $this->repository->getFiltered($data);
        return InventoryItemResource::collection($data);
    }

}