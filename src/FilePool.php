<?php
namespace Pyncer\Cache;

use Pyncer\Cache\AbstractPool;
use Pyncer\Cache\CacheItem;

use function file_exists;
use function file_put_contents;
use function Pyncer\date_time as pyncer_date_time;
use function Pyncer\Http\base64_encode as pyncer_http_base64_encode;
use function Pyncer\IO\delete as pyncer_io_delete;
use function Pyncer\IO\delete_contents as pyncer_io_delete_contents;
use function var_export;

use const DIRECTORY_SEPARATOR as DS;

class FilePool extends AbstractPool
{
    protected $dir;

    public function __construct(string $dir)
    {
        $this->dir = $dir;
    }

    /**
     * {@inheritdoc}
     */
    public function getItem($key)
    {
        $key = $this->defendKey($key);

        $name = pyncer_http_base64_encode($key);

        $file = $this->dir . DS . $name . '.php';

        if (!file_exists($file)) {
            return $this->getEmptyItem($key);
        }

        $item = include $file;

        $item['expiration'] = (
            ($item['expiration'] ?? null) ?
            pyncer_date_time($item['expiration']) :
            null
        );

        $item['hit'] = (
            $item['expiration'] === null ||
            $item['expiration'] > pyncer_date_time()
        );

        return new CacheItem($key, $item);
    }

    /**
     * {@inheritdoc}
     */
    public function clear()
    {
        pyncer_io_delete_contents($this->dir);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteItems(array $keys)
    {
        foreach ($keys as $key) {
            $key = $this->defendKey($key);

            $name = pyncer_http_base64_encode($key);

            $file = $this->directroy . DS . $name . '.php';

            if (!file_exists($file)) {
                pyncer_io_delete($file);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function hasItem($key)
    {
        $key = $this->defendKey($key);

        $name = pyncer_http_base64_encode($key);

        $file = $this->directroy . DS . $name . '.php';

        if (!file_exists($file)) {
            return false;
        }

        $item = include $file;

        if (!$item) {
            return false;
        }

        return (
            ($item['expiration'] ?? null) === null ||
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
            $name = pyncer_http_base64_encode($key);

            $file = $this->directroy . DS . $name . '.php';

            $date = $item->getExpiration();

            file_put_contents($file, var_export([
                'value' => $item->getRawValue(),
                'expiration' => ($date ? $date->format('Y-m-d H:i:s') : null),
            ], true));
        }

        return true;
    }
}
