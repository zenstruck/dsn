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
final class Group extends Wrapped
{
    /**
     * @param \Stringable[] $children
     */
    public function __construct(Scheme $scheme, Query $query, ?string $fragment, private array $children)
    {
        parent::__construct($scheme, $query, $fragment);
    }

    /**
     * @return \Stringable[]
     */
    public function children(): array
    {
        return $this->children;
    }

    protected function innerString(): string
    {
        return \implode(' ', $this->children);
    }
}
