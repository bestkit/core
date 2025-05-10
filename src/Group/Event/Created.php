<?php

namespace Bestkit\Group\Event;

use Bestkit\Group\Group;
use Bestkit\User\User;

class Created
{
    /**
     * @var \Bestkit\Group\Group
     */
    public $group;

    /**
     * @var User
     */
    public $actor;

    /**
     * @param Group $group
     * @param User $actor
     */
    public function __construct(Group $group, User $actor = null)
    {
        $this->group = $group;
        $this->actor = $actor;
    }
}
