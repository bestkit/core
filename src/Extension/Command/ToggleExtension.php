<?php

namespace Bestkit\Extension\Command;

use Bestkit\User\User;

class ToggleExtension
{
    /**
     * @var User
     */
    public $actor;

    /**
     * @var string
     */
    public $name;

    /**
     * @var bool
     */
    public $enabled;

    public function __construct(User $actor, string $name, bool $enabled)
    {
        $this->actor = $actor;
        $this->name = $name;
        $this->enabled = $enabled;
    }
}
