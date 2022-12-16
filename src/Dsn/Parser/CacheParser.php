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

use Psr\Cache\CacheItemPoolInterface as Psr6Cache;
use Symfony\Contracts\Cache\CacheInterface as SymfonyCache;
use Zenstruck\Dsn\Parser;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class CacheParser implements Parser
{
    public function __construct(private Parser $parser, private SymfonyCache|Psr6Cache $cache)
    {
    }

    public function parse(string $dsn): \Stringable
    {
        // according to https://stackoverflow.com/a/3665527, this is the fastest hash
        $key = 'dsn-'.\hash('crc32', $dsn);

        if ($this->cache instanceof SymfonyCache) {
            return $this->cache->get($key, fn() => $this->parser->parse($dsn));
        }

        // Psr6Cache
        $item = $this->cache->getItem($key);

        if (!$item->isHit()) {
            $this->cache->save($item->set($this->parser->parse($dsn)));
        }

        return $item->get();
    }
}
