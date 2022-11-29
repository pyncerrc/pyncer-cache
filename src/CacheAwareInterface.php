<?php
namespace pyncer\framework\log;

use Psr\Cache\CacheItemPoolInterface as PsrCacheItemPoolInterface;

interface CacheAwareInterface
{
    /**
     * Sets a cache item pool.
     */
    public function setCache(?PsrCacheItemPoolInterface $value): static;
}
