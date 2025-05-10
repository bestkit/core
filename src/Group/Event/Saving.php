<?php

namespace Bestkit\Group\Event;

use Bestkit\Group\Group;
use Bestkit\User\User;

class Saving
{
    /**
     * The group that will be saved.
     *
     * @var \Bestkit\Group\Group
     */
    public $group;

    /**
     * The user who is performing the action.
     *
     * @var User
     */
    public $actor;

    /**
     * The attributes to update on the group.
     *
     * @var array
     */
    public $data;

    /**
     * @param Group $group The group that will be saved.
     * @param User $actor The user who is performing the action.
     * @param array $data The attributes to update on the group.
     */
    public function __construct(Group $group, User $actor, array $data)
    {
        $this->group = $group;
        $this->actor = $actor;
        $this->data = $data;
    }
}
