<?php

namespace Bestkit\Post\Exception;

use Exception;
use Bestkit\Foundation\KnownError;

class FloodingException extends Exception implements KnownError
{
    public function getType(): string
    {
        return 'too_many_requests';
    }
}
