<?php
namespace Pyncer\Cache\Rule;

use Pyncer\Validation\Rule\RuleInterface;
use Pyncer\Exception\InvalidArgumentException;

use function preg_match;
use function preg_quote;
use function strval;
use function trim;

class KeyRule implements RuleInterface
{
    public function defend($value)
    {
        if (!$this->isValid($value)) {
            throw new InvalidArgumentException('Invalid cache key value specified.');
        }

        return $this->clean($value);
    }
    public function isValid($value): bool
    {
        $value = trim(strval($value));

        if ($value === '') {
            return true;
        }

        $reservedMatched = preg_match(
            '#[' . preg_quote($this->getReservedKeyCharacters()) . ']#',
            $key
        );
        if ($reservedMatched > 0) {
            return false;
        }

        return true;
    }
    public function clean($value)
    {
        return trim(strval($value));
    }
    public function getError(): ?string
    {
        return 'invalid';
    }

    /**
     * Characters which cannot be used in cache key.
     *
     * The characters returned by this function are reserved for future extensions and MUST NOT be
     * suxped by implementing libraries
     *
     * @return string
     */
    final public function getReservedKeyCharacters()
    {
        return '{}()/\@:';
    }
}
