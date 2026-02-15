<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\LowStockDetected;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

final class NotifyWarehouseManager implements ShouldQueue
{
    use InteractsWithQueue;

    public int $tries = 3;


    public int $backoff = 60;

    public function handle(LowStockDetected $event): void
    {
        Log::warning("Low stock detected for Item: {$event->stock->inventory_item_id} in Warehouse: {$event->stock->warehouse_id}", [
            'current_quantity' => $event->currentQuantity,
            'threshold' => $event->threshold,
            'detected_at' => $event->detectedAt->format('Y-m-d H:i:s'),
        ]);

    }

    public function failed(LowStockDetected $event, \Throwable $exception): void
    {
        Log::error("Failed to process low stock event for stock ID: {$event->stock->id}", [
            'error' => $exception->getMessage()
        ]);
    }
}