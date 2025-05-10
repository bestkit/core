<?php

namespace Bestkit\Http\Filter;

use Bestkit\Filter\AbstractFilterer;
use Bestkit\Http\AccessToken;
use Bestkit\User\User;
use Illuminate\Database\Eloquent\Builder;

class AccessTokenFilterer extends AbstractFilterer
{
    protected function getQuery(User $actor): Builder
    {
        return AccessToken::query()->whereVisibleTo($actor);
    }
}
