<?php

namespace Zenstruck\Dsn\Parser;

use Zenstruck\Dsn\Exception\UnableToParse;
use Zenstruck\Dsn\Parser;
use Zenstruck\Uri;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class UriParser implements Parser
{
    public function parse(string $dsn): Uri
    {
        try {
            return Uri::new($dsn);
        } catch (\InvalidArgumentException $e) {
            throw UnableToParse::value($dsn, $e);
        }
    }
}
