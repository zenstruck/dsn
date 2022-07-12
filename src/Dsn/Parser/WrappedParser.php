<?php

namespace Zenstruck\Dsn\Parser;

use Zenstruck\Dsn\Decorated;
use Zenstruck\Dsn\Exception\UnableToParse;
use Zenstruck\Dsn\Group;
use Zenstruck\Dsn\Parser;
use Zenstruck\Dsn\Wrapped;
use Zenstruck\Uri\Query;
use Zenstruck\Uri\Scheme;

/**
 * Parses strings like "name(dsn1 dsn2)" into a "Group" dsn and
 * strings like "name(dsn)" into a "Decorated" dsn.
 *
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class WrappedParser implements Parser, ParserAware
{
    private Parser $parser;

    public function parse(string $dsn): Wrapped
    {
        if (!\preg_match('#^([\w+-]+)\((.+)\)(\?.+)?$#', $dsn, $matches)) {
            throw UnableToParse::value($dsn);
        }

        $scheme = new Scheme($matches[1]);
        $query = new Query($matches[3] ?? '');

        if (1 === \count(\explode(' ', $matches[2]))) {
            return new Decorated($scheme, $query, $this->parser()->parse($matches[2]));
        }

        return new Group(
            $scheme,
            $query,
            \array_map(fn(string $dsn) => $this->parser()->parse($dsn), self::explode($matches[2]))
        );
    }

    public function setParser(Parser $parser): void
    {
        $this->parser = $parser;
    }

    private function parser(): Parser
    {
        return $this->parser ?? throw new \LogicException('Parser not set.');
    }

    /**
     * Explodes the groups by space but keeps nested groups together.
     *
     * @return string[]
     */
    private static function explode(string $value): array
    {
        $nest = 0;
        $parts = [];
        $part = '';

        foreach (\mb_str_split($value) as $char) {
            if (' ' === $char && 0 === $nest) {
                $parts[] = $part;
                $part = '';

                continue;
            }

            if ('(' === $char) {
                ++$nest;
            }

            if (')' === $char) {
                --$nest;
            }

            $part .= $char;
        }

        $parts[] = $part;

        return $parts;
    }
}
