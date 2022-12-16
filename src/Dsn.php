<?php

/*
 * This file is part of the zenstruck/dsn package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zenstruck;

use Zenstruck\Dsn\Decorated;
use Zenstruck\Dsn\Group;
use Zenstruck\Dsn\Parser\ChainParser;
use Zenstruck\Uri\Mailto;

/**
 * Helper for parsing DSN objects provided by this component.
 *
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class Dsn
{
    private static ChainParser $defaultParser;

    public static function parse(string $value): Uri|Mailto|Group|Decorated
    {
        return (self::$defaultParser ??= new ChainParser())->parse($value); // @phpstan-ignore-line
    }
}
