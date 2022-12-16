<?php

/*
 * This file is part of the zenstruck/dsn package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zenstruck\Dsn\Parser;

use Zenstruck\Dsn\Parser;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
interface ParserAware
{
    public function setParser(Parser $parser): void;
}
