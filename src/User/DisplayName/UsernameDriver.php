<?php

namespace Bestkit\User\DisplayName;

use Bestkit\User\User;

/**
 * The default driver, which returns the user's username.
 */
class UsernameDriver implements DriverInterface
{
    public function displayName(User $user): string
    {
        return $user->username;
    }
}
