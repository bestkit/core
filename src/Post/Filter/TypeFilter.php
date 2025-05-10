<?php

namespace Bestkit\Post\Filter;

use Bestkit\Filter\FilterInterface;
use Bestkit\Filter\FilterState;
use Bestkit\Filter\ValidateFilterTrait;

class TypeFilter implements FilterInterface
{
    use ValidateFilterTrait;

    public function getFilterKey(): string
    {
        return 'type';
    }

    public function filter(FilterState $filterState, $filterValue, bool $negate)
    {
        $type = $this->asString($filterValue);

        $filterState->getQuery()->where('posts.type', $negate ? '!=' : '=', $type);
    }
}
