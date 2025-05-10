<?php

namespace Bestkit\Notification\Command;

use Bestkit\User\User;

class DeleteAllNotifications
{
    /**
     * The user performing the action.
     *
     * @var User
     */
    public $actor;

    /**
     * @param User $actor The user performing the action.
     */
    public function __construct(User $actor)
    {
        $this->actor = $actor;
    }
}
