<?php

namespace Bestkit\Http\Exception;

use Exception;
use Bestkit\Foundation\KnownError;

class TokenMismatchException extends Exception implements KnownError
{
    public function getType(): string
    {
        return 'csrf_token_mismatch';
    }
}
