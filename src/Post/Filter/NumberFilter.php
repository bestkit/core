<?php

namespace Bestkit\Post\Filter;

use Bestkit\Filter\FilterInterface;
use Bestkit\Filter\FilterState;
use Bestkit\Filter\ValidateFilterTrait;

class NumberFilter implements FilterInterface
{
    use ValidateFilterTrait;

    public function getFilterKey(): string
    {
        return 'number';
    }

    public function filter(FilterState $filterState, $filterValue, bool $negate)
    {
        $number = $this->asInt($filterValue);

        $filterState->getQuery()->where('posts.number', $negate ? '!=' : '=', $number);
    }
}
