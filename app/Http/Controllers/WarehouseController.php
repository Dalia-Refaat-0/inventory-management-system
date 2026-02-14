<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Contracts\WarehouseRepositoryInterface;
use App\Http\Resources\WarehouseResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class WarehouseController extends Controller
{
    public function __construct(
        private readonly WarehouseRepositoryInterface $repository,
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $perPage = min((int) $request->input('per_page', 15), 100);

        return WarehouseResource::collection(
            $this->repository->getActive($perPage)
        );
    }
}