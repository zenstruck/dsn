<?php

/*
 * This file is part of the zenstruck/dsn package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zenstruck\Dsn;

use Zenstruck\Dsn\Exception\UnableToParse;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
interface Parser
{
    /**
     * Convert a string to a "DSN" object.
     *
     * @throws UnableToParse
     */
    public function parse(string $dsn): \Stringable;
}
