<?php

namespace Zenstruck\Dsn\Parser;

use Zenstruck\Dsn\Parser;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
interface ParserAware
{
    public function setParser(Parser $parser): void;
}
