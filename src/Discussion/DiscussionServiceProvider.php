<?php

namespace Bestkit\Discussion;

use Bestkit\Discussion\Access\ScopeDiscussionVisibility;
use Bestkit\Discussion\Event\Renamed;
use Bestkit\Foundation\AbstractServiceProvider;
use Illuminate\Contracts\Events\Dispatcher;

class DiscussionServiceProvider extends AbstractServiceProvider
{
    public function boot(Dispatcher $events)
    {
        $events->subscribe(DiscussionMetadataUpdater::class);
        $events->subscribe(UserStateUpdater::class);

        $events->listen(
            Renamed::class,
            DiscussionRenamedLogger::class
        );

        Discussion::registerVisibilityScoper(new ScopeDiscussionVisibility(), 'view');
    }
}
