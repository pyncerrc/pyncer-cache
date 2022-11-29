<?php
namespace Pyncer\Cache;

use Psr\Cache\CacheItemInterface as PsrCacheItemInterface;
use Psr\Cache\CacheItemPoolInterface as PsrCacheItemPoolInterface;
use Pyncer\Cache\CacheItem;
use Pyncer\Cache\Rule\KeyRule;
use Pyncer\Cache\Exception\InvalidArgumentException;
use Pyncer\Validation\Rule\NonEmptyRule;
use Pyncer\Validation\ValueValidator;

use function array_map;

abstract class AbstractPool implements PsrCacheItemPoolInterface
{
    /**
     * Deferred cache items to be saved later.
     */
    protected array $deferred = [];
    protected ValueValidator $validator;

    public function __construct()
    {
        $this->validator = new ValueValidator();
        $this->validator->AddRules(
            new KeyRule(),
            new NonEmptyRule()
        );
    }

    /**
     * {@inheritdoc}
     */
    public function save(PsrCacheItemInterface $item): bool
    {
        return $this->write([$item]);
    }

    /**
     * {@inheritdoc}
     */
    public function saveDeferred(PsrCacheItemInterface $item): bool
    {
        $this->deferred[] = $item;
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function commit(): bool
    {
        $success = $this->write($this->deferred);
        if ($success) {
            $this->deferred = [];
        }
        return $success;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteItem(string $key): bool
    {
        return $this->deleteItems([$key]);
    }

    /**
     * Determines if the specified key is legal under PSR-6.
     *
     * @param string $key
     *   The key to validate.
     * @throws InvalidArgumentException
     *   An exception implementing The Cache InvalidArgumentException interface
     *   will be thrown if the key does not validate.
     * @return string
     *   The cleaned key if legal.
     */
    public function defendKey(string $key): string
    {
        if (!$this->validator->isValid($key)) {
            throw new InvalidArgumentException(
                'Invalid cache key value specified.'
            );
        }

        return $this->validator->clean($key);
    }

    /**
     * Returns an empty item definition.
     *
     * @param string $key
     *   The key for which to return the empty Cache Item.
     *
     * @return array
     */
    protected function getEmptyItem(string $key): PsrCacheItemInterface
    {
        $key = $this->defendKey($key);

        return new CacheItem($key, [
            'value' => null,
            'expiration' => null,
            'hit' => false,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getItems(array $keys = []): iterable
    {
        // This method will throw an appropriate exception if any key is not valid.
        $keys = array_map([$this, 'defendKey'], $keys);

        $collection = [];

        foreach ($keys as $key) {
            $collection[$key] = $this->getItem($key);
        }

        return $collection;
    }

    abstract protected function write(array $items);
}
