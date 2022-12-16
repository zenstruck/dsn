<?php

/*
 * This file is part of the zenstruck/dsn package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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
