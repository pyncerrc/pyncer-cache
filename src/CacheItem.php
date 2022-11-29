<?php
namespace Pyncer\Cache;

use DateInterval;
use DateTime;
use DateTimeInterface;
use Psr\Cache\CacheItemInterface as PsrCacheItemInterface;
use Pyncer\Cache\Exception\InvalidArgumentException;

use function get_class;
use function gettype;
use function is_null;
use function is_numeric;
use function is_object;
use function Pyncer\date_time as pyncer_date_time;

class CacheItem implements PsrCacheItemInterface
{
    protected string $key;
    protected mixed $value;
    protected bool $hit;
    protected ?DateTime $expiration;

    /**
     * Constructs a new DatabaseCacheItem.
     *
     * @param string $key
     *   The key of the cache item this object represents.
     * @param array $data
     *   An associative array of data representing the cache item.
     */
    public function __construct(string $key, ?array $data = null)
    {
        $this->key = $key;
        $this->value = $data['value'] ?? null;
        $this->hit = $data['hit'] ?? false;
        $this->expiration = $data['expiration'] ?? null;
    }

    /**
     * {@inheritdoc}
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * {@inheritdoc}
     */
    public function get(): mixed
    {
        return ($this->isHit() ? $this->value : null);
    }

    /**
     * {@inheritdoc}
     */
    public function set(mixed $value = null): static
    {
        $this->value = $value;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function isHit(): bool
    {
        return $this->hit;
    }

    /**
     * Returns the expiration timestamp.
     *
     * @return \DateTime
     *   The timestamp at which this cache item should expire.
     *
     * @internal
     */
    public function getExpiration(): DateTime
    {
        return $this->expiration;
    }

    /**
     * Returns the raw value, regardless of hit status.
     *
     * @return mixed
     *
     * @internal
     */
    public function getRawValue(): mixed
    {
        return $this->value;
    }

    /**
     * {@inheritdoc}
     */
    public function expiresAt(?DateTimeInterface $expiration): static
    {
        if (is_null($expiration)) {
            $this->expiration = null;
        } elseif ($expiration instanceof DateTimeInterface) {
            $this->expiration = $expiration;
        } else {
            throw new InvalidArgumentException(sprintf(
                'Expiration of \'%s\' cache item must be null or an instance of DateTimeInterface. \'%s\' given.',
                $this->getKey(),
                (is_object($expiration) ? get_class($expiration) : gettype($expiration))
            ));
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function expiresAfter(null|int|DateInterval $time): static
    {
        if (is_null($time)) {
            $this->expiration = null;
        } elseif (is_numeric($time)) {
            $time =  new DateInterval('PT' . $time . 'S');

            $expiration =  pyncer_date_time();
            $expiration->add($time);
            $this->expiration = $expiration;
        } elseif ($time instanceof DateInterval) {
            $expiration = pyncer_date_time();
            $expiration->add($time);
            $this->expiration = $expiration;
        } else {
            throw new InvalidArgumentException(sprintf(
                'Expiration time of \'%s\' cache item must be null, an integer, or an instance of DateInterval. \'%s\' given.',
                $this->getKey(),
                (is_object($time) ? get_class($time) : gettype($time))
            ));
        }

        return $this;
    }
}
