<?php

declare(strict_types=1);

namespace App\Providers;

use App\Events\LowStockDetected;
use App\Listeners\NotifyWarehouseManager;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        LowStockDetected::class => [
            NotifyWarehouseManager::class,
        ],
    ];

    public function boot(): void
    {
        //
    }
}