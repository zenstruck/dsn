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

use Zenstruck\Dsn\Exception\UnableToParse;
use Zenstruck\Dsn\Parser;
use Zenstruck\Uri;
use Zenstruck\Uri\ParsedUri;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class UriParser implements Parser
{
    public function parse(string $dsn): Uri
    {
        try {
            return ParsedUri::wrap($dsn)->normalize();
        } catch (\InvalidArgumentException $e) {
            throw UnableToParse::value($dsn, $e);
        }
    }
}
