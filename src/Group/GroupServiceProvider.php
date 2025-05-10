<?php

namespace Bestkit\Group;

use Bestkit\Foundation\AbstractServiceProvider;
use Bestkit\Group\Access\ScopeGroupVisibility;

class GroupServiceProvider extends AbstractServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function boot()
    {
        Group::registerVisibilityScoper(new ScopeGroupVisibility(), 'view');
    }
}
