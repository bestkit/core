<?php

namespace Bestkit\Discussion\Event;

use Bestkit\Discussion\UserState;
use Bestkit\User\User;

class UserRead
{
    /**
     * @var UserState
     */
    public $state;

    /**
     * @var User
     */
    public $actor;

    /**
     * @param UserState $state
     */
    public function __construct(UserState $state)
    {
        $this->state = $state;
    }
}
