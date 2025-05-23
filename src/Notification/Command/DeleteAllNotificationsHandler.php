<?php

namespace Bestkit\Notification\Command;

use Bestkit\Notification\Event\DeletedAll;
use Bestkit\Notification\NotificationRepository;
use Bestkit\User\Exception\NotAuthenticatedException;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\Carbon;

class DeleteAllNotificationsHandler
{
    /**
     * @var NotificationRepository
     */
    protected $notifications;

    /**
     * @var Dispatcher
     */
    protected $events;

    /**
     * @param NotificationRepository $notifications
     * @param Dispatcher $events
     */
    public function __construct(NotificationRepository $notifications, Dispatcher $events)
    {
        $this->notifications = $notifications;
        $this->events = $events;
    }

    /**
     * @param DeleteAllNotifications $command
     * @throws NotAuthenticatedException
     */
    public function handle(DeleteAllNotifications $command)
    {
        $actor = $command->actor;

        $actor->assertRegistered();

        $this->notifications->deleteAll($actor);

        $this->events->dispatch(new DeletedAll($actor, Carbon::now()));
    }
}
