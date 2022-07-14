<?php

namespace Zenstruck\Dsn\Tests\Bridge\Symfony;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Zenstruck\Dsn\Parser\CacheParser;
use Zenstruck\Dsn\Tests\Fixture\Service;
use Zenstruck\Uri;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class ZenstruckDsnBundleTest extends KernelTestCase
{
    /**
     * @test
     */
    public function cache_parser_is_autowired(): void
    {
        $parser = self::getContainer()->get(Service::class)->parser;

        $this->assertInstanceOf(CacheParser::class, $parser);

        $parsed = $parser->parse('scheme:?foo=bar');

        $this->assertInstanceOf(Uri::class, $parsed);
        $this->assertSame('scheme:?foo=bar', (string) $parsed);
    }
}
