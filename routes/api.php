<?php

declare(strict_types=1);

use App\Http\Controllers\AuthController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\StockTransferController;
use App\Http\Controllers\WarehouseController;
use App\Http\Controllers\WarehouseInventoryController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/inventory', [InventoryController::class, 'index']);

    Route::get('/warehouses', [WarehouseController::class, 'index']);

    Route::get('/warehouses/{warehouseId}/inventory', [WarehouseInventoryController::class, 'index']);

    Route::post('/stock-transfers', [StockTransferController::class, 'store']);
});