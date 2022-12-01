<?php

namespace Zenstruck\Dsn\Tests\Parser;

use PHPUnit\Framework\TestCase;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface as Psr6Cache;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Zenstruck\Dsn\Exception\UnableToParse;
use Zenstruck\Dsn\Parser\CacheParser;
use Zenstruck\Dsn\Parser\ChainParser;
use Zenstruck\Uri\Mailto;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class CacheParserTest extends TestCase
{
    /**
     * @test
     * @dataProvider cacheProvider
     */
    public function throws_exception_if_unable_to_parse(Psr6Cache $cache): void
    {
        $parser = new CacheParser(new ChainParser(), $cache);

        try {
            $parser->parse('null://');
        } catch (UnableToParse) {
        }

        $this->expectException(UnableToParse::class);

        $parser->parse('null://');
    }

    /**
     * @test
     * @dataProvider cacheProvider
     */
    public function can_cache_result(Psr6Cache $cache): void
    {
        $parser = new CacheParser(new ChainParser(), $cache);

        $parsed1 = $parser->parse('mailto:sally@example.com');
        $parsed2 = $parser->parse('mailto:sally@example.com');

        $this->assertEquals($parsed1, $parsed2);

        self::changeCacheValue($cache, 'mailto:sally@example.com', $new = Mailto::new('john@example.com'));

        $this->assertEquals($new, $parser->parse('mailto:sally@example.com'));
    }

    public static function cacheProvider(): iterable
    {
        yield [new ArrayAdapter()]; // Symfony Cache
        yield [
            new class() implements Psr6Cache {
                private ArrayAdapter $c;

                public function __construct()
                {
                    $this->c = new ArrayAdapter();
                }

                public function getItem($key): CacheItemInterface
                {
                    return $this->c->getItem($key);
                }

                public function getItems($keys = []): iterable
                {
                    return $this->c->getItems($keys);
                }

                public function hasItem($key): bool
                {
                    return $this->c->hasItem($key);
                }

                public function clear(): bool
                {
                    return $this->c->clear();
                }

                public function deleteItem($key): bool
                {
                    return $this->c->deleteItem($key);
                }

                public function deleteItems($keys): bool
                {
                    return $this->c->deleteItems($keys);
                }

                public function save(CacheItemInterface $item): bool
                {
                    return $this->c->save($item);
                }

                public function saveDeferred(CacheItemInterface $item): bool
                {
                    return $this->c->saveDeferred($item);
                }

                public function commit(): bool
                {
                    return $this->c->commit();
                }
            },
        ];
    }

    private static function changeCacheValue(Psr6Cache $cache, string $key, mixed $new): void
    {
        $key = 'dsn-'.\hash('crc32', $key);
        $item = $cache->getItem($key);
        $cache->save($item->set($new));
    }
}
