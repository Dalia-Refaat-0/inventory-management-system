<?php

declare(strict_types=1);

namespace App\Services\Cache;

use Closure;
use Illuminate\Cache\CacheManager;
use Illuminate\Contracts\Cache\Repository;

final class CacheService
{
    private Repository $store;
    private bool $enabled;
    private array $lockConfig;

    public function __construct(CacheManager $manager)
    {
        $this->enabled    = (bool) config('inventory.cache.enabled', true);
        $this->lockConfig = config('inventory.cache.lock', ['timeout' => 10, 'wait' => 5]);
        $this->store      = $manager->store(config('inventory.cache.store', 'redis'));
    }

    private function execute(Closure $callback, mixed $default = null): mixed
    {
        if ($this->enabled) {
            return $callback();
        }
        return $default instanceof Closure ? $default() : $default;
    }

    public function tags(array $tags): Repository
    {
        return method_exists($this->store, 'tags') 
            ? $this->store->tags($tags) 
            : $this->store;
    }


    public function remember(array $tags, string $key, int $ttl, Closure $callback): mixed
    {
        return $this->execute(
            fn() => $this->tags($tags)->remember($key, $ttl, $callback),
            $callback
        );
    }


    public function rememberWithLock(array $tags, string $key, int $ttl, Closure $callback): mixed
    {
        if (!$this->enabled) {
            return $callback();
        }

        $tagged = $this->tags($tags);
        
        if (null !== ($cached = $tagged->get($key))) {
            return $cached;
        }

        return $this->store->lock("lock:{$key}", $this->lockConfig['timeout'])
            ->block($this->lockConfig['wait'], function () use ($tagged, $key, $ttl, $callback) {
                return $tagged->remember($key, $ttl, $callback);
            });
    }

    public function put(array $tags, string $key, mixed $value, int $ttl): void
    {
        $this->execute(fn() => $this->tags($tags)->put($key, $value, $ttl));
    }

    public function forget(array $tags, string $key): void
    {
        $this->execute(fn() => $this->tags($tags)->forget($key));
    }

    public function flush(array $tags): void
    {
        $this->execute(fn() => $this->tags($tags)->flush());
    }

    public function has(array $tags, string $key): bool
    {
        return (bool) $this->execute(fn() => $this->tags($tags)->has($key), false);
    }
}