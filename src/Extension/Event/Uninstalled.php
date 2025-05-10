<?php

namespace Bestkit\Extension\Event;

use Bestkit\Extension\Extension;

class Uninstalled
{
    /**
     * @var Extension
     */
    public $extension;

    /**
     * @param Extension $extension
     */
    public function __construct(Extension $extension)
    {
        $this->extension = $extension;
    }
}
