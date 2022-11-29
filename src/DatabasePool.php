<?php
namespace Pyncer\Cache;

use Pyncer\Cache\AbstractPool;
use Pyncer\Cache\CacheItem;
use Pyncer\Database\ConnectionInterface;

use function Pyncer\date_time as pyncer_date_time;

class DatabasePool extends AbstractPool
{
    protected ConnectionInterface $connection;
    protected string $table;

    public function __construct(ConnectionInterface $connection, string $table)
    {
        $this->connection = $connection;
        $this->table = $table;
    }

    /**
     * {@inheritdoc}
     */
    public function getItem($key)
    {
        // This method will either return True or throw an appropriate exception.
        $key = $this->defendKey($key);

        $item = $this->connection
            ->select($this->table)
            ->where(['key' => $key])
            ->limit(1)
            ->execute()
            ->getRow();

        if (!$item) {
            return $this->getEmptyItem($key);
        }

        $item['expiration'] = (
            $item['expiration'] ?
            pyncer_date_time($item['expiration']) :
            null
        );

        return new CacheItem($key, [
            'value' => $item['value'],
            'expiration' => $item['expiration'],
            'hit' => (
                $item['expiration'] === null ||
                $item['expiration'] > pyncer_date_time()
            )
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function clear()
    {
        return $this->connection
            ->delete($this->table)
            ->execute();
    }

    /**
     * {@inheritdoc}
     */
    public function deleteItems(array $keys)
    {
        return $this->connection
            ->delete($this->table)
            ->where(['key' => $keys])
            ->execute();
    }

    /**
     * {@inheritdoc}
     */
    public function hasItem($key)
    {
        $item = $this->connection
            ->select($this->table)
            ->where(['key' => $key])
            ->limit(1)
            ->execute()
            ->getRow();

        if (!$item) {
            return false;
        }

        return (
            $item['expiration'] === null ||
            pyncer_date_time($item['expiration']) > pyncer_date_time()
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function write(array $items)
    {
        /** @var \Psr\Cache\CacheItemInterface $item */
        foreach ($items as $item) {
            if ($this->connection
                ->select($this->table)
                ->where(['key' => $item->getKey()])
                ->limit(1)
                ->execute()
                ->getRow()
            ) {
                $this->connection
                    ->update($this->table)
                    ->values([
                        'value' => $item->getRawValue(),
                        'expiration' => $this->connection->dateTime($item->getExpiration()),
                    ])
                    ->where(['key' => $item->getKey()])
                    ->execute();
            } else {
                $this->connection
                    ->insert($this->table)
                    ->values([
                        'key' => $item->getKey(),
                        'value' => $item->getRawValue(),
                        'expiration' => $this->connection->dateTime($item->getExpiration()),
                    ])
                    ->execute();
            }
        }

        return true;
    }
}
