<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\Stock;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class LowStockDetected
{
    use Dispatchable, InteractsWithSockets, SerializesModels;


    public function __construct(
        public readonly Stock $stock,
        public readonly int $currentQuantity,
        public readonly int $threshold,
        public readonly \DateTimeImmutable $detectedAt
    ) {}


    public static function dispatchFor(Stock $stock): void
    {
        static::dispatch(
            $stock,
            $stock->quantity,
            $stock->low_stock_threshold ?? 10,
            new \DateTimeImmutable()
        );
    }
}