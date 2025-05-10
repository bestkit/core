<?php

namespace Bestkit\Discussion\Event;

use Bestkit\Discussion\Discussion;
use Bestkit\User\User;

class Restored
{
    /**
     * @var \Bestkit\Discussion\Discussion
     */
    public $discussion;

    /**
     * @var User
     */
    public $actor;

    /**
     * @param \Bestkit\Discussion\Discussion $discussion
     * @param User $actor
     */
    public function __construct(Discussion $discussion, User $actor = null)
    {
        $this->discussion = $discussion;
        $this->actor = $actor;
    }
}
