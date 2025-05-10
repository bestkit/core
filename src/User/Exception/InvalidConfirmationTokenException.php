<?php

namespace Bestkit\User\Exception;

use Exception;
use Bestkit\Foundation\KnownError;

class InvalidConfirmationTokenException extends Exception implements KnownError
{
    public function getType(): string
    {
        return 'invalid_confirmation_token';
    }
}
