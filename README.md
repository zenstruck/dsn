# zenstruck/dsn

[![CI](https://github.com/zenstruck/dsn/actions/workflows/ci.yml/badge.svg)](https://github.com/zenstruck/dsn/actions/workflows/ci.yml)
[![codecov](https://codecov.io/gh/zenstruck/dsn/branch/1.x/graph/badge.svg?token=uXk5xgPh7I)](https://codecov.io/gh/zenstruck/dsn)

DSN parsing library with support for complex expressions:

1. [**URI**](#uri): `http://example.com?foo=bar#baz`
2. [**Mailto**](#mailto): `mailto:sam@example.com?cc=jane@example.com`
3. _DSN Functions_:
   1. [**Decorated**](#decorated): `retry(inner://dsn)?times=5`
   2. [**Group**](#group): `round+robin(inner://dsn1 inner://dsn2)`
   3. [**Complex**](#complex-dsns): `fail+over(rount+robin(inner://dsn1 inner://dsn2) inner://dsn3)`

## Installation

```bash
composer require zenstruck/dsn
```

## Usage

### Parsing DSNs

For basic usage, you can use `Zenstruck\Dsn::parse($mydsn)`. This takes a `string`
and returns _one of_ the following objects:

- [`Zenstruck\Uri`](#uri)
- [`Zenstruck\Uri\Mailto`](#mailto)
- [`Zenstruck\Dsn\Decorated`](#decorated)
- [`Zenstruck\Dsn\Group`](#group)

The only thing in common with these returned objects is that they are all `\Stringable`.

If the parsing fails, a `Zenstruck\Dsn\Exception\UnableToParse` exception will be thrown.

> **Note**
> See [`zenstruck/uri`](https://github.com/zenstruck/uri) to view the API for `Uri|Mailto`.

#### URI

This DSN object is an instance of `Zenstruck\Uri`. View it's
[full API documentation](https://github.com/zenstruck/uri#parsingreading-uris).

```php
$dsn = Zenstruck\Dsn::parse('https://example.com/some/dir/file.html?q=abc&flag=1#test')

/* @var Zenstruck\Uri $dsn */
$dsn->scheme()->toString(); // 'https'
$dsn->host()->toString(); // 'example.com'
$dsn->path()->toString(); // /some/dir/file.html
$dsn->query()->all(); // ['q' => 'abc', 'flag' => '1']
$dsn->fragment(); // 'test'
```

#### Mailto

This DSN object is an instance of `Zenstruck\Uri\Mailto`. View it's
[full API documentation](https://github.com/zenstruck/uri#mailto-uris).

```php
$dsn = Zenstruck\Dsn::parse('mailto:kevin@example.com?cc=jane@example.com&subject=some+subject&body=some+body')

/** @var Zenstruck\Uri\Mailto $dsn */
$dsn->to(); // ['kevin@example.com']
$dsn->cc(); // ['jane@example.com']
$dsn->bcc(); // []
$dsn->subject(); // 'some subject'
$dsn->body(); // 'some body'
```

#### Decorated

This is a _DSN Function_ that wraps a single _inner_ DSN:

```
retry(inner://dsn)?times=5
```

The above example would parse to a `Zenstruck\Dsn\Decorated` object with
the following properties:

* _Scheme/Function Name_: `retry`
* _Query_: `['times' => '5']`
* _Inner DSN_: This will be an instance of `Zenstruck\Uri` in this case but could be
  any [_DSN Object_](#parsing-dsns).

```php
$dsn = Zenstruck\Dsn::parse('retry(inner://dsn)?times=5');

/** @var Zenstruck\Dsn\Decorated $dsn */
$dsn->scheme()->toString(); // 'retry'
$dsn->query()->all(); // ['times' => '5']

$inner = $dsn->inner();

/** @var Zenstruck\Uri $inner */
$inner->scheme()->toString(); // 'inner'
$inner->host()->toString(); // 'dsn'
```

#### Group

This is a _DSN Function_ that wraps a multiple _inner_ DSNs (space separated):

```
round+robin(inner://dsn1 inner://dsn2)?strategy=random
```

The above example would parse to a `Zenstruck\Dsn\Group` object with
the following properties:

* _Scheme/Function Name_: `round+robin`
* _Query_: `['strategy' => 'random']`
* _Child DSNs_: This will be an `array` of _2_ `Zenstruck\Uri` objects in this case but could an array
  of any [_DSN Objects_](#parsing-dsns).

```php
$dsn = Zenstruck\Dsn::parse('round+robin(inner://dsn1 inner://dsn2)?strategy=random');

/** @var Zenstruck\Dsn\Group $dsn */
$dsn->scheme()->toString(); // 'round+robin'
$dsn->query()->all(); // ['strategy' => 'random']

$children = $dsn->children();

/** @var Zenstruck\Uri[] $children */
$children[0]->scheme()->toString(); // 'inner'
$children[0]->host()->toString(); // 'dsn1'

$children[1]->scheme()->toString(); // 'inner'
$children[1]->host()->toString(); // 'dsn2'
```

#### Complex DSNs

You can nest [Group](#group) and [Decorated](#decorated) DSNs to create complex expressions:

```php
$dsn = Zenstruck\Dsn::parse('retry(round+robin(inner://dsn1 inner://dsn2)?strategy=random)?times=5');

/** @var Zenstruck\Dsn\Decorated $dsn */
$dsn->scheme()->toString(); // 'retry'
$dsn->query()->all(); // ['times' => '5']

$inner = $dsn->inner();

/** @var Zenstruck\Dsn\Group $inner */
$inner->scheme()->toString(); // 'round+robin'
$inner->query()->all(); // ['strategy' => 'random']

$children = $inner->children();

/** @var Zenstruck\Uri[] $children */
$children[0]->scheme()->toString(); // 'inner'
$children[0]->host()->toString(); // 'dsn1'

$children[1]->scheme()->toString(); // 'inner'
$children[1]->host()->toString(); // 'dsn2'
```

### Using Parsed DSNs

Once parsed, you can use an `instanceof` check to determine the type of DSN that
was parsed and act accordingly:

```php
$dsn = Zenstruck\Dsn::parse($someDsnString); // throws Zenstruck\Dsn\Exception\UnableToParse on failure

switch (true) {
    case $dsn instanceof Zenstruck\Uri:
        // do something with the Uri object

    case $dsn instanceof Zenstruck\Uri\Mailto:
        // do something with the Mailto object

    case $dsn instanceof Decorated:
        // do something with the Decorated object (see api below)

    case $dsn instanceof Group:
        // do something with the Group object (see api below)
}
```

#### Usage Example

The best way to show how the parsed DSN could be used for something useful
is with an example. Consider an email abstraction library that has multiple
_service transports_ (**smtp**, **mailchimp**, **postmark**) and special _utility transports_:
**round-robin** (for distributing workload between multiple transports) and **retry**
(for retrying failures x times before hard-failing).

You'd like end user's of this library to be able to create transports from a
custom DSN syntax. The following is an example of a transport DSN factory:

```php
use Zenstruck\Dsn\Decorated;
use Zenstruck\Dsn\Group;
use Zenstruck\Uri;

class TransportFactory
{
    public function create(\Stringable $dsn): TransportInterface
    {
        if ($dsn instanceof Uri && $dsn->scheme()->equals('smtp')) {
            return new SmtpTransport(
                host: $dsn->host()->toString(),
                user: $dsn->user(),
                password: $dsn->pass(),
                port: $dsn->port(),
            );
        }

        if ($dsn instanceof Uri && $dsn->scheme()->equals('mailchimp')) {
            return new MailchimpTransport(apiKey: $dsn->user());
        }

        if ($dsn instanceof Uri && $dsn->scheme()->equals('postmark')) {
            return new PostmarkTransport(apiKey: $dsn->user());
        }

        if ($dsn instanceof Decorated && $dsn->scheme()->equals('retry')) {
            return new RetryTransport(
                transport: $this->create($dsn->inner()), // recursively build inner transport
                times: $dsn->query()->getInt('times', 5), // default to 5 retries if not set
            );
        }

        if ($dsn instanceof Group && $dsn->scheme()->equals('round+robin')) {
            return new RoundRobinTransport(
                transports: array_map(fn($dsn) => $this->create($dsn), $dsn->children()), // recursively build inner transports
                strategy: $dsn->query()->get('strategy', 'random'), // default to "random" strategy if not set
            );
        }

        throw new \LogicException("Unable to parse transport DSN: {$dsn}.");
    }
}
```

The usage of this factory is as follows:

```php
use Zenstruck\Dsn;

// SmtpTransport:
$factory->create('smtp://kevin:p4ssword@localhost');

// RetryTransport wrapping SmtpTransport:
$factory->create('retry(smtp://kevin:p4ssword@localhost)');

// RetryTransport (3 retries) wrapping RoundRobinTransport (sequential strategy) wrapping MailchimpTransport & PostmarkTransport
$factory->create('retry(round+robin(mailchimp://key@default postmark://key@default)?strategy=sequential)?times=3');
```

## Advanced Usage

Under the hood `Zenstruck\Dsn::parse()` uses a parsing system for converting DSN
strings to the packaged [DSN objects](#parsing-dsns). You can create your own
parsers by having them implement the `Zenstruck\Dsn\Parser` interface.

> **Note**
> `Zenstruck\Dsn::parse()` is a utility function that only uses the
> [core parsers](#core-parsers). In order to add your [own parsers](#custom-parsers),
> you'll need to manually wire up a [chain parser](#chainparser) that includes them
> and use this for parsing DSNs.

### Core Parsers

#### `UriParser`

Converts url-looking strings to [`Zenstruck\Uri`](#uri) objects.

#### `MailtoParser`

Converts mailto-looking strings to [`Zenstruck\Uri\Mailto`](#mailto) objects.

#### `WrappedParser`

Converts dsn-function-looking strings to [`Zenstruck\Dsn\Decorated`](#decorated) or
[`Zenstruck\Dsn\Group`](#group) objects.

### Utility Parsers

#### `ChainParser`

Wraps a chain of parsers, during `parse()` it loops through these and
attempts to find one that successfully parses a DSN string. It is considered
successful if a `\Stringable` object is returned. If the parser throws a
`Zenstruck\Dsn\Exception\UnableToParse` exception, the next parser in the
chain is tried. Finally, if all the parsers throw `UnableToParse`, this is
thrown.

```php
$parser = new Zenstruck\Dsn\Parser\ChainParser([$customParser1, $customParser1]);

$parser->parse('some-dsn'); // \Stringable object
```

> **Note**
> This parser always contains the [core parsers](#core-parsers) as the last items in
> the chain. [Custom parsers](#custom-parsers) you add to the constructor are attempted
> before these.

#### `CacheParser`

Wraps another parser and an instance of one of these cache interfaces:

- `Symfony\Contracts\Cache\CacheInterface` (Symfony cache)
- `Psr\Cache\CacheItemPoolInterface` (PSR-6 cache)
- `Psr\SimpleCache\CacheInterface` (PSR-16 cache)

The parsed object is cached (keyed by the DSN string) and subsequent
parsing of the same string are retrieved from the cache. This gives a
bit of a performance boost especially for [complex DSNs](#complex-dsns).

```php
/** @var SymfonyCache|Psr6Cache|Psr16Cache $cache */
/** @var Zenstruck\Dsn\Parser $inner */

$parser = new \Zenstruck\Dsn\Parser\CacheParser($parser, $cache);

$parser->parse('some-dsn'); // \Stringable (caches this object)

$parser->parse('some-dsn'); // \Stringable (retrieved from cache)
```

### Custom Parsers

You can create your own parser by creating an object that implements
`Zenstruck\Dsn\Parser`:

```php
use Zenstruck\Dsn\Exception\UnableToParse;
use Zenstruck\Dsn\Parser;

class MyParser implements Parser
{
    public function parse(string $dsn): \Stringable
    {
        // determine if $dsn is parsable and return a \Stringable DSN object

        throw UnableToParse::value($dsn); // important when using in a chain parser
    }
}
```

Usage:

```php
// standalone
$parser = new MyParser();

$parser->parse('some-dsn');

// add to ChainParser
$parser = new Zenstruck\Dsn\Parser\ChainParser([new MyParser()]);

$parser->parse('some-dsn');
```

## Symfony Bundle

A Symfony Bundle is provided that adds an autowireable `Zenstruck\Dsn\Parser` service.
This is an interface with a `parse(string $dsn)` method. It works identically
to `Zenstruck\Dsn::parse()` but caches the created _DSN object_ (using `cache.system`)
for a bit of a performance boost.

To use, enable the bundle:

```php
// config/bundles.php

return [
    // ...
    Zenstruck\Dsn\Bridge\Symfony\ZenstruckDsnBundle::class => ['all' => true],
];
```

`Zenstruck\Dsn\Parser` can be autowired:

```php
use Zenstruck\Dsn\Parser;

public function myAction(Parser $parser): Response
{
    // ...

    $dsn = $parser->parse(...);

    // ...
}
```

### DSN Service Factory

You can use the `Zenstruck\Dsn\Parser` service as a service factory to create
DSN service objects:

```yaml
# config/services.yaml

services:
    mailer_dsn:
        factory: ['@Zenstruck\Dsn\Parser', 'parse']
        arguments: ['%env(MAILER_DSN)%']
```

The `mailer_dsn` service will be an instance of a parsed DSN object. The type
depends on the value of the `MAILER_DSN` environment variable.

Using the [mailer transport factory above](#usage-example), we can create the
transport via a service factory that uses the `mailer_dsn`:

```yaml
# config/services.yaml

services:
    App\Mailer\TransportFactory: ~

    App\Mailer\TransportInterface:
        factory: ['@App\Mailer\TransportFactory', 'create']
        arguments: ['@mailer_dsn']
```

Now, when injecting `App\Mailer\TransportInterface`, the transport will be
created by `App\Mailer\TransportFactory` using your `MAILER_DSN` environment
variable.
