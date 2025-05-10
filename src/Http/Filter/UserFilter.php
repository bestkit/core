<?php

namespace Bestkit\Http\Filter;

use Bestkit\Api\Controller\ListAccessTokensController;
use Bestkit\Filter\FilterInterface;
use Bestkit\Filter\FilterState;
use Bestkit\Filter\ValidateFilterTrait;

/**
 * Filters an access tokens request by the related user.
 *
 * @see ListAccessTokensController
 */
class UserFilter implements FilterInterface
{
    use ValidateFilterTrait;

    /**
     * @inheritDoc
     */
    public function getFilterKey(): string
    {
        return 'user';
    }

    /**
     * @inheritDoc
     */
    public function filter(FilterState $filterState, $filterValue, bool $negate)
    {
        $filterValue = $this->asInt($filterValue);

        $filterState->getQuery()->where('user_id', $negate ? '!=' : '=', $filterValue);
    }
}
