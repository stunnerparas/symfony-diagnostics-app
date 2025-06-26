<?php

namespace App\Service;

use Psr\Cache\CacheItemPoolInterface;
use Symfony\Contracts\Cache\CacheInterface;

/**
 * Decorator for SystemDiagnosticsCollector to add caching and implements the decorator pattern.
 */
class CachedDiagnosticsCollector implements SystemDiagnosticsCollectorInterface
{
    private SystemDiagnosticsCollectorInterface $collector; // Uses the interface now
    private CacheInterface $cache;
    private int $cacheTtl;

    public function __construct(
        SystemDiagnosticsCollectorInterface $collector,
        CacheInterface $cache,
        int $cacheTtl = 300
    ) {
        $this->collector = $collector;
        $this->cache = $cache;
        $this->cacheTtl = $cacheTtl;
    }

    /**
     * Collects diagnostics, with caching.
     */
    public function collect(array $include = []): array
    {
        $cacheKey = 'diagnostics_' . md5(serialize($include));

        return $this->cache->get($cacheKey, function () use ($include) {
            return $this->collector->collect($include);
        }, $this->cacheTtl);
    }


    public function getAvailableProviders(): array
    {
        return $this->collector->getAvailableProviders();
    }
}
