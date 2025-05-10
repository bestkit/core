<?php

namespace Bestkit\Http\Access;

use Bestkit\Http\AccessToken;
use Bestkit\User\Access\AbstractPolicy;
use Bestkit\User\User;

class AccessTokenPolicy extends AbstractPolicy
{
    public function revoke(User $actor, AccessToken $token)
    {
        if ($token->user_id === $actor->id || $actor->hasPermission('moderateAccessTokens')) {
            return $this->allow();
        }
    }
}
