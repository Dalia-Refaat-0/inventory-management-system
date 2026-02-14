<?php

declare(strict_types=1);

namespace App\Providers;

use App\Contracts\WarehouseRepositoryInterface;
use App\Repositories\Decorators\CachingWarehouseRepository;
use App\Repositories\Eloquent\EloquentWarehouseRepository;
use App\Services\Cache\CacheService;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{

    public function register(): void
    {

        $this->app->singleton(WarehouseRepositoryInterface::class, function ($app) {
            return new CachingWarehouseRepository(
                $app->make(EloquentWarehouseRepository::class), 
                $app->make(CacheService::class)                 
            );
        });

    }
}