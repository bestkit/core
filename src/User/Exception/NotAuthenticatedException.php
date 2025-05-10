<?php

namespace Bestkit\User\Exception;

use Exception;
use Bestkit\Foundation\KnownError;

class NotAuthenticatedException extends Exception implements KnownError
{
    public function getType(): string
    {
        return 'not_authenticated';
    }
}
