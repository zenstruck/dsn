<?php

namespace Zenstruck\Dsn\Parser;

use Psr\Cache\CacheItemPoolInterface as Psr6Cache;
use Psr\SimpleCache\CacheInterface as Psr16Cache;
use Symfony\Contracts\Cache\CacheInterface as SymfonyCache;
use Zenstruck\Dsn\Parser;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class CacheParser implements Parser
{
    public function __construct(private Parser $parser, private SymfonyCache|Psr6Cache|Psr16Cache $cache)
    {
    }

    public function parse(string $dsn): mixed
    {
        // according to https://stackoverflow.com/a/3665527, this is the fastest hash
        $key = 'dsn-'.\hash('crc32', $dsn);

        if ($this->cache instanceof SymfonyCache) {
            return $this->cache->get($key, fn() => $this->parser->parse($dsn));
        }

        if ($this->cache instanceof Psr16Cache) {
            if ($this->cache->has($key)) {
                return $this->cache->get($key);
            }

            $this->cache->set($key, $ret = $this->parser->parse($dsn));

            return $ret;
        }

        // Psr6Cache
        $item = $this->cache->getItem($key);

        if (!$item->isHit()) {
            $this->cache->save($item->set($this->parser->parse($dsn)));
        }

        return $item->get();
    }
}
