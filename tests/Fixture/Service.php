<?php

namespace Zenstruck\Dsn\Tests\Fixture;

use Zenstruck\Dsn\Parser;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class Service
{
    public function __construct(public Parser $parser)
    {
    }
}
