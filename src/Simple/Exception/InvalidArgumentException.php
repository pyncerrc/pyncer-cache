<?php
namespace Pyncer\Cache\Simple\Exception;

use Psr\SimpleCache\InvalidArgumentException as PsrInvalidArgumentException;
use Pyncer\Exception\InvalidArgumentException as PyncerInvalidArgumentException;

/**
 * Exception interface for invalid cache arguments.
 *
 * Any time an invalid argument is passed into a method it must throw an
 * exception class which implements Psr\Cache\InvalidArgumentException.
 */
class InvalidArgumentException extends PyncerInvalidArgumentException implements
    PsrInvalidArgumentException
{}
