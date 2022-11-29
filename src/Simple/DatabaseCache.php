<?php
namespace Pyncer\Cache\Simple;

use DateTimeInterface;
use Pyncer\Cache\DatabasePool;
use Pyncer\Cache\Simple\AbstractCache;
use Pyncer\Database\ConnectionInterface;

class DatabaseCache extends AbstractCache
{
    public function __construct(
        ConnectionInterface $connection,
        string $table,
        ?DateTimeInterface $dateTime = null
    ) {
       $pool = new DatabasePool($connection, $table);

       parent::__construct($pool, $dateTime);
    }
}
