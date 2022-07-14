<?php

namespace Zenstruck\Dsn\Parser;

use Zenstruck\Dsn\Exception\UnableToParse;
use Zenstruck\Dsn\Parser;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class ChainParser implements Parser
{
    /** @var Parser[] */
    private static array $defaultParsers;

    /**
     * @param Parser[] $parsers
     */
    public function __construct(private iterable $parsers = [])
    {
    }

    public function parse(string $dsn): \Stringable
    {
        foreach ($this->parsers() as $parser) {
            if ($parser instanceof ParserAware) {
                $parser->setParser($this);
            }

            try {
                return $parser->parse($dsn);
            } catch (UnableToParse) {
                continue;
            }
        }

        throw UnableToParse::value($dsn);
    }

    /**
     * @return Parser[]
     */
    private function parsers(): iterable
    {
        yield from $this->parsers;
        yield from self::defaultParsers();
    }

    /**
     * @return Parser[]
     */
    private static function defaultParsers(): array
    {
        return self::$defaultParsers ??= [
            new WrappedParser(),
            new MailtoParser(),
            new UriParser(),
        ];
    }
}
