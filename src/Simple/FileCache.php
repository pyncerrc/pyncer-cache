<?php
namespace Pyncer\Cache\Simple;

use DateTimeInterface;
use Pyncer\Cache\FilePool;
use Pyncer\Cache\Simple\AbstractCache;

class FileCache extends AbstractCache
{
    public function __construct(
        string $dir,
        ?DateTimeInterface $dateTime = null
    ) {
       $pool = new FilePool($dir);

       parent::__construct($pool, $dateTime);
    }
}
