<?php

/*
 * This file is part of the zenstruck/dsn package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zenstruck\Dsn;

use Zenstruck\Uri\Part\Query;
use Zenstruck\Uri\Part\Scheme;

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
