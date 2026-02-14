<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Contracts\WarehouseRepositoryInterface;
use App\Http\Controllers\Controller;
use App\Http\Resources\WarehouseResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class WarehouseController extends Controller
{
    public function __construct(
        private readonly WarehouseRepositoryInterface $repository,
    ) {}

    public function index(): AnonymousResourceCollection
    {
        return WarehouseResource::collection(
            $this->repository->getActive()
        );
    }
}