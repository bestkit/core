<?php

namespace Bestkit\Discussion\Event;

use Bestkit\Discussion\Discussion;
use Bestkit\User\User;

class Saving
{
    /**
     * The discussion that will be saved.
     *
     * @var \Bestkit\Discussion\Discussion
     */
    public $discussion;

    /**
     * The user who is performing the action.
     *
     * @var User
     */
    public $actor;

    /**
     * Any user input associated with the command.
     *
     * @var array
     */
    public $data;

    /**
     * @param \Bestkit\Discussion\Discussion $discussion
     * @param User $actor
     * @param array $data
     */
    public function __construct(Discussion $discussion, User $actor, array $data = [])
    {
        $this->discussion = $discussion;
        $this->actor = $actor;
        $this->data = $data;
    }
}
