<?php

namespace Bestkit\Http\Exception;

use Exception;
use Bestkit\Foundation\KnownError;

class MethodNotAllowedException extends Exception implements KnownError
{
    public function getType(): string
    {
        return 'method_not_allowed';
    }
}
