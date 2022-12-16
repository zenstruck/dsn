<?php

/*
 * This file is part of the zenstruck/dsn package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zenstruck\Dsn\Tests\Bridge\Symfony;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Zenstruck\Dsn\Group;
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

    /**
     * @test
     * @dataProvider factoryProvider
     */
    public function can_use_parser_as_factory(string $dsn, string $class): void
    {
        $_SERVER['MY_DSN'] = $dsn;

        $dsnService = self::getContainer()->get('my_dsn');

        $this->assertInstanceOf($class, $dsnService);
        $this->assertSame($dsn, (string) $dsnService);
    }

    public static function factoryProvider(): iterable
    {
        yield ['some://dsn', Uri::class];
        yield ['chain(some://dsn1 retry(some://dsn2)?times=5)', Group::class];
    }
}
