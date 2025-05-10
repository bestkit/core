<?php

namespace Bestkit\Discussion\Event;

use Bestkit\Discussion\UserState;

class UserDataSaving
{
    /**
     * @var \Bestkit\Discussion\UserState
     */
    public $state;

    /**
     * @param \Bestkit\Discussion\UserState $state
     */
    public function __construct(UserState $state)
    {
        $this->state = $state;
    }
}
