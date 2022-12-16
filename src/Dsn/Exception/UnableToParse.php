<?php

/*
 * This file is part of the zenstruck/dsn package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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
