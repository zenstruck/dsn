<?php

namespace Zenstruck\Dsn\Exception;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class UnableToParse extends \RuntimeException
{
    public static function value(string $dsn, ?\Throwable $previous = null): self
    {
        return new self(\sprintf('Unable to parse DSN: "%s".', $dsn), 0, $previous);
    }
}
