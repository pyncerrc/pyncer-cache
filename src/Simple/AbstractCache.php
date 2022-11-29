<?php
namespace Pyncer\Cache\Simple;

use DateTimeInterface;
use DateTime;
use DateInterval;
use Psr\Cache\CacheItemPoolInterface as PsrCacheItemPoolInterface;
use Psr\SimpleCache\CacheInterface as PsrCacheInterface;
use Pyncer\Cache\CacheItem;
use Pyncer\Cache\Exception\InvalidArgumentException;
use Pyncer\Cache\Simple\Exception\InvalidArgumentException as SimpleInvalidArgumentException;
use Traversable;

use function array_keys;
use function array_map;
use function iterator_to_array;
use function Pyncer\date_time as pyncer_date_time;

abstract class AbstractCache implements PsrCacheInterface
{
    protected PsrCacheItemPoolInterface $pool;
    protected ?DateTimeInterface $dateTime;

    public function __construct(
        PsrCacheItemPoolInterface $pool,
        ?DateTimeInterface $dateTime = null
    ) {
        $this->pool = $pool;
        $this->dateTime = $dateTime;
    }

    /**
     * {@inheritdoc}
     */
    public function get(string $key, mixed $default = null): mixed;
    {
        try {
            $item = $this->pool->getItem($key);
        } catch (InvalidArgumentException $e) {
            throw new SimpleInvalidArgumentException(
                $e->getMessage(),
                $e->getCode(),
                $e
            );
        }

        if ($item->isHit()) {
            return $item->get();
        }

        return $default;
    }

    /**
     * {@inheritdoc}
     */
    public function set(
        string $key,
        mixed $value,
        null|int|DateInterval $ttl = null
    ): bool
    {
        try {
            $key = $this->pool->defendKey($key);
        } catch (InvalidArgumentException $e) {
            throw new SimpleInvalidArgumentException(
                $e->getMessage(),
                $e->getCode(),
                $e
            );
        }

        $ttlDateTime = $this->getTtlDateTime($ttl);

        $cacheItem = new CacheItem($key, [
            'value' => $value,
            'expiration' => $ttlDateTime
        ]);

        return $this->pool->save($cacheItem);
    }

    /**
     * {@inheritdoc}
     */
    public function delete(string $key): bool
    {
        try {
            $result = $this->pool->deleteItem($key);
        } catch (InvalidArgumentException $e) {
            throw new SimpleInvalidArgumentException(
                $e->getMessage(),
                $e->getCode(),
                $e
            );
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function clear(): bool
    {
        return $this->pool->clear();
    }

    /**
     * {@inheritdoc}
     */
    public function getMultiple(
        iterable $keys,
        mixed $default = null
    ): iterable
    {
        $keys = $this->defendKeys($keys);

        $collection = [];

        foreach ($keys as $key) {
            $collection[$key] = $this->get($key, $default);
        }

        return $collection;
    }

    /**
     * {@inheritdoc}
     */
    public function setMultiple(
        iterable $values,
        null|int|DateInterval $ttl = null
    ): bool
    {
        if ($values instanceof Traversable) {
            $values = iterator_to_array($values, true);
        }

        $keys = array_keys($values);
        $keys = $this->defendKeys($keys);

        try {
            $keys = array_map([$this->pool, 'defendKey'], $keys);
        } catch (InvalidArgumentException $e) {
            throw new SimpleInvalidArgumentException(
                $e->getMessage(),
                $e->getCode(),
                $e
            );
        }

        $ttlDateTime = $this->getTtlDateTime($ttl);

        $success = true;

        foreach ($values as $key => $value) {
            $cacheItem = new CacheItem($key, [
                'value' => $value,
                'expiration' => $ttlDateTime
            ]);

            if (!$this->pool->save($cacheItem)) {
                $success = false;
            }
        }

        return $success;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteMultiple(iterable $keys): bool
    {
        if ($keys instanceof Traversable) {
            $keys = iterator_to_array($keys, false);
        }

        try {
            $result = $this->pool->deleteItems($keys);
        } catch (InvalidArgumentException $e) {
            throw new SimpleInvalidArgumentException(
                $e->getMessage(),
                $e->getCode(),
                $e
            );
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function has(string $key): bool
    {
        try {
            $result = $this->pool->hasItem($key);
        } catch (InvalidArgumentException $e) {
            throw new SimpleInvalidArgumentException(
                $e->getMessage(),
                $e->getCode(),
                $e
            );
        }

        return $result;
    }

    protected function defendKeys(iterable $keys): array
    {
        if ($keys instanceof Traversable) {
            $keys = iterator_to_array($keys, false);
        }

        return $keys;
    }

    protected function getTtlDateTime(null|int|DateInterval $ttl): ?DateTimeInterface
    {
        if ($ttl === null) {
            return null;
        }

        if ($this->dateTime !== null) {
            $date = new DateTime(
                '@' . $this->dateTime->getTimestamp()
            );
            $date->setTimezone($this->dateTime->getTimezone());
        } else {
            $date = pyncer_date_time();
        }

        if (is_int($ttl)) {
            $ttl =  new DateInterval('PT' . $ttl . 'S');
        }

        $date->add($ttl);

        return $date;
    }
}
