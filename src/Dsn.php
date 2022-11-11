<?php

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
