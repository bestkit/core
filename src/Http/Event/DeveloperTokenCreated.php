<?php

namespace Bestkit\Http\Event;

use Bestkit\Http\AccessToken;

class DeveloperTokenCreated
{
    /**
     * @var AccessToken
     */
    public $token;

    public function __construct(AccessToken $token)
    {
        $this->token = $token;
    }
}
