<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\LowStockDetected;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

final class NotifyWarehouseManager implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * تحديد عدد محاولات إعادة التشغيل في حال الفشل
     */
    public int $tries = 3;

    /**
     * تحديد وقت التأخير بين المحاولات (بالثواني)
     */
    public int $backoff = 60;

    public function handle(LowStockDetected $event): void
    {
        // 1. تسجيل الحدث في الـ Logs لأغراض الـ Audit
        Log::warning("Low stock detected for Item: {$event->stock->inventory_item_id} in Warehouse: {$event->stock->warehouse_id}", [
            'current_quantity' => $event->currentQuantity,
            'threshold' => $event->threshold,
            'detected_at' => $event->detectedAt->format('Y-m-d H:i:s'),
        ]);

        // 2. إرسال إشعار (مثلاً عبر Slack أو Email)
        // Notification::send($event->stock->warehouse->manager, new LowStockNotification($event->stock));
    }

    /**
     * التعامل مع الفشل النهائي للـ Listener
     */
    public function failed(LowStockDetected $event, \Throwable $exception): void
    {
        Log::error("Failed to process low stock event for stock ID: {$event->stock->id}", [
            'error' => $exception->getMessage()
        ]);
    }
}