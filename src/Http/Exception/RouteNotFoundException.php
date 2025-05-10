<?php

namespace Bestkit\Http\Exception;

use Exception;
use Bestkit\Foundation\KnownError;

class RouteNotFoundException extends Exception implements KnownError
{
    public function getType(): string
    {
        return 'not_found';
    }
}
