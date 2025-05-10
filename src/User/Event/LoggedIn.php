<?php

namespace Bestkit\User\Event;

use Bestkit\Http\AccessToken;
use Bestkit\User\User;

class LoggedIn
{
    public $user;

    public $token;

    public function __construct(User $user, AccessToken $token)
    {
        $this->user = $user;
        $this->token = $token;
    }
}
