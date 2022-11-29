<?php
namespace pyncer\framework\log;

use Psr\Cache\CacheItemPoolInterface as PsrCacheItemPoolInterface;

trait CacheAwareTrait
{
    protected $cache;

    protected function getCache(): ?PsrCacheItemPoolInterface
    {
        return $this->cache;
    }

    /**
     * Sets a cache item pool.
     */
    public function setCache(?PsrCacheItemPoolInterface $value): static
    {
        $this->cache = $value;
        return $this;
    }
}
