<?php

namespace Bestkit\Group\Access;

use Bestkit\User\Access\AbstractPolicy;
use Bestkit\User\User;

class GroupPolicy extends AbstractPolicy
{
    /**
     * @param User $actor
     * @param string $ability
     * @return bool|null
     */
    public function can(User $actor, $ability)
    {
        if ($actor->hasPermission('group.'.$ability)) {
            return $this->allow();
        }
    }
}
