<?php
namespace Pyncer\Cache\Simple\Exception;

use Psr\SimpleCache\CacheException as PsrCacheException;
use Pyncer\Exception\Exception;

/**
 * Exception for all exceptions thrown by an Implementing Library.
 */
class CacheException extends \Exception implements
    PsrCacheException,
    Exception
{}
