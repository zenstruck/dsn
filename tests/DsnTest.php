<?php

namespace Zenstruck\Dsn\Tests;

use PHPUnit\Framework\TestCase;
use Zenstruck\Dsn;
use Zenstruck\Dsn\Decorated;
use Zenstruck\Dsn\Exception\UnableToParse;
use Zenstruck\Dsn\Group;
use Zenstruck\Uri;
use Zenstruck\Uri\Mailto;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class DsnTest extends TestCase
{
    /**
     * @test
     * @dataProvider validValueProvider
     */
    public function can_parse_valid_value($input, $expectedClass, $expectedString): void
    {
        $dsn = Dsn::parse($input);

        $this->assertInstanceOf($expectedClass, $dsn);
        $this->assertSame($expectedString, (string) $dsn);
    }

    public static function validValueProvider(): iterable
    {
        yield ['mailto:kevin@example.com', Mailto::class, 'mailto:kevin%40example.com'];
        yield ['http://www.example.com', Uri::class, 'http://www.example.com'];
        yield ['null', Uri::class, 'null'];
        yield ['scheme:', Uri::class, 'scheme:'];
        yield ['scheme:?foo=bar', Uri::class, 'scheme:?foo=bar'];
        yield ['failover(smtp://default mail+api://default)?foo=bar', Group::class, 'failover(smtp://default mail+api://default)?foo=bar'];
        yield ['fail+over(smtp://default roundrobin(mail+api://default postmark+api://default))', Group::class, 'fail+over(smtp://default roundrobin(mail+api://default postmark+api://default))'];
        yield ['failover()', Uri::class, 'failover%28%29'];
        yield ['fail+over(smtp://default)?foo=bar', Decorated::class, 'fail+over(smtp://default)?foo=bar'];
        yield ['fail-over(smtp://default)?foo=bar', Decorated::class, 'fail-over(smtp://default)?foo=bar'];
    }

    /**
     * @test
     * @dataProvider invalidValueProvider
     */
    public function cannot_parse_invalid_value($input): void
    {
        $this->expectException(UnableToParse::class);
        $this->expectExceptionMessage(\sprintf('Unable to parse DSN: "%s".', $input));

        Dsn::parse($input);
    }

    public static function invalidValueProvider(): iterable
    {
        yield ['null://'];
        yield ['mailto://'];
    }

    /**
     * @test
     */
    public function can_parse_group_dsn(): void
    {
        $dsn = Dsn::parse('fail+over(smtp://default mail+api://default)');

        $this->assertInstanceOf(Group::class, $dsn);
        $this->assertEmpty($dsn->query()->all());
        $this->assertSame('fail+over', $dsn->scheme()->toString());
        $this->assertInstanceOf(Uri::class, $dsn->children()[0]);
        $this->assertSame('smtp', $dsn->children()[0]->scheme()->toString());
        $this->assertInstanceOf(Uri::class, $dsn->children()[1]);
        $this->assertSame('mail+api', $dsn->children()[1]->scheme()->toString());
    }

    /**
     * @test
     */
    public function can_parse_nested_group_dsn(): void
    {
        /** @var Group $dsn */
        $dsn = Dsn::parse('failover(smtp://default round+robin(mail+api://default?foo=bar#hash mailto:kevin) failover(mail+api://default roundrobin(mail+api://default)))');

        $this->assertInstanceOf(Group::class, $dsn);
        $this->assertEmpty($dsn->query()->all());
        $this->assertCount(3, $dsn->children());
        $this->assertInstanceOf(Uri::class, $dsn->children()[0]);
        $this->assertSame('smtp://default', (string) $dsn->children()[0]);
        $this->assertInstanceOf(Group::class, $dsn->children()[1]);
        $this->assertSame('round+robin(mail+api://default?foo=bar#hash mailto:kevin)', (string) $dsn->children()[1]);
        $this->assertCount(2, $dsn->children()[1]->children());
        $this->assertCount(2, $dsn->children()[2]->children());
        $this->assertInstanceOf(Decorated::class, $dsn->children()[2]->children()[1]);
        $this->assertInstanceOf(Uri::class, $dsn->children()[2]->children()[1]->inner());
        $this->assertSame('mail+api://default?foo=bar#hash', $dsn->children()[1]->children()[0]->toString());
    }

    /**
     * @test
     */
    public function can_parse_group_dsn_with_parameters(): void
    {
        /** @var Group $dsn */
        $dsn = Dsn::parse('fail+over(smtp://default mail+api://default)?a=b&c=d');

        $this->assertInstanceOf(Group::class, $dsn);
        $this->assertSame(['a' => 'b', 'c' => 'd'], $dsn->query()->all());
        $this->assertSame('fail+over', $dsn->scheme()->toString());
        $this->assertInstanceOf(Uri::class, $dsn->children()[0]);
        $this->assertSame('smtp', $dsn->children()[0]->scheme()->toString());
        $this->assertInstanceOf(Uri::class, $dsn->children()[1]);
        $this->assertSame('mail+api', $dsn->children()[1]->scheme()->toString());
    }

    /**
     * @test
     */
    public function can_parse_nested_group_dsn_with_parameters(): void
    {
        $dsn = Dsn::parse('failover(smtp://default roundrobin(mail+api://default mailto:kevin)?a=b&c=d fail+over(mail+api://default round+robin(mail+api://default)?e=f&g=h)?i=j&k=l)?m=n&o=p');

        $this->assertInstanceOf(Group::class, $dsn);
        $this->assertSame(['m' => 'n', 'o' => 'p'], $dsn->query()->all());
        $this->assertSame(['a' => 'b', 'c' => 'd'], $dsn->children()[1]->query()->all());
        $this->assertSame(['i' => 'j', 'k' => 'l'], $dsn->children()[2]->query()->all());
        $this->assertSame(['e' => 'f', 'g' => 'h'], $dsn->children()[2]->children()[1]->query()->all());
    }

    /**
     * @test
     */
    public function can_parse_decorated_dsn(): void
    {
        $dsn = Dsn::parse('fail+over(smtp://default)?foo=bar');

        $this->assertInstanceOf(Decorated::class, $dsn);
        $this->assertSame('fail+over', $dsn->scheme()->toString());
        $this->assertInstanceOf(Uri::class, $dsn->inner());
        $this->assertSame('smtp://default', $dsn->inner()->toString());
        $this->assertSame(['foo' => 'bar'], $dsn->query()->all());
    }

    /**
     * @test
     */
    public function can_parse_scheme_dsn(): void
    {
        $dsn = Dsn::parse('in-memory:');

        $this->assertInstanceOf(Uri::class, $dsn);
        $this->assertSame('in-memory', $dsn->scheme()->toString());
        $this->assertSame([], $dsn->query()->all());

        $dsn = Dsn::parse('in-memory:?foo=bar');

        $this->assertInstanceOf(Uri::class, $dsn);
        $this->assertSame('in-memory', $dsn->scheme()->toString());
        $this->assertSame(['foo' => 'bar'], $dsn->query()->all());
    }
}
