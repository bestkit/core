<?php

namespace Bestkit\Foundation;

interface SiteInterface
{
    /**
     * Create and boot a Bestkit application instance.
     *
     * @return AppInterface
     */
    public function bootApp(): AppInterface;
}
