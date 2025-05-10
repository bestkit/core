<?php

namespace Bestkit\Api\Exception;

use Exception;
use Bestkit\Foundation\KnownError;

class InvalidAccessTokenException extends Exception implements KnownError
{
    public function getType(): string
    {
        return 'invalid_access_token';
    }
}
