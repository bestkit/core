<?php

namespace Bestkit\User\Exception;

use Exception;
use Bestkit\Foundation\KnownError;

class PermissionDeniedException extends Exception implements KnownError
{
    public function getType(): string
    {
        return 'permission_denied';
    }
}
