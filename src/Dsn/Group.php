<?php

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
