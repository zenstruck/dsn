<?php

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
