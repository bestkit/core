<?php

namespace Bestkit\Http;

use Bestkit\User\User;

class ActorReference
{
    /**
     * @var User
     */
    private $actor;

    public function setActor(User $actor)
    {
        $this->actor = $actor;
    }

    public function getActor(): User
    {
        return $this->actor;
    }
}
