<?php

namespace Bestkit\Group\Filter;

use Bestkit\Filter\FilterInterface;
use Bestkit\Filter\FilterState;
use Bestkit\Filter\ValidateFilterTrait;

class HiddenFilter implements FilterInterface
{
    use ValidateFilterTrait;

    public function getFilterKey(): string
    {
        return 'hidden';
    }

    public function filter(FilterState $filterState, $filterValue, bool $negate)
    {
        $hidden = $this->asBool($filterValue);

        $filterState->getQuery()->where('is_hidden', $negate ? '!=' : '=', $hidden);
    }
}
