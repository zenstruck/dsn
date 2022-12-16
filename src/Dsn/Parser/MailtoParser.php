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
use Zenstruck\Uri\Mailto;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class MailtoParser implements Parser
{
    public function parse(string $dsn): Mailto
    {
        if (!\str_starts_with($dsn, 'mailto:')) {
            throw UnableToParse::value($dsn);
        }

        try {
            return Mailto::new($dsn);
        } catch (\InvalidArgumentException $e) {
            throw UnableToParse::value($dsn, $e);
        }
    }
}
