<?php

namespace Zenstruck\Dsn;

use Zenstruck\Uri\Query;
use Zenstruck\Uri\Scheme;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class Decorated extends Wrapped
{
    public function __construct(Scheme $scheme, Query $query, ?string $fragment, private \Stringable $inner)
    {
        parent::__construct($scheme, $query, $fragment);
    }

    public function inner(): \Stringable
    {
        return $this->inner;
    }

    protected function innerString(): string
    {
        return $this->inner();
    }
}
