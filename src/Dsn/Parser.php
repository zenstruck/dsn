<?php

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
    public function parse(string $dsn): mixed;
}
