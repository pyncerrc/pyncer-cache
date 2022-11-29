<?php
namespace Pyncer\Cache\Exception;

use Psr\Cache\CacheException as PsrCacheException;
use Pyncer\Exception\Exception;

/**
 * Exception for all exceptions thrown by an Implementing Library.
 */
class CacheException extends \Exception implements
    PsrCacheException,
    Exception
{}
