<?php

namespace Bestkit\User\Command;

use Bestkit\Foundation\DispatchEventsTrait;
use Bestkit\User\AvatarUploader;
use Bestkit\User\Event\AvatarDeleting;
use Bestkit\User\UserRepository;
use Illuminate\Contracts\Events\Dispatcher;

class DeleteAvatarHandler
{
    use DispatchEventsTrait;

    /**
     * @var UserRepository
     */
    protected $users;

    /**
     * @var AvatarUploader
     */
    protected $uploader;

    /**
     * @param Dispatcher $events
     * @param UserRepository $users
     * @param AvatarUploader $uploader
     */
    public function __construct(Dispatcher $events, UserRepository $users, AvatarUploader $uploader)
    {
        $this->events = $events;
        $this->users = $users;
        $this->uploader = $uploader;
    }

    /**
     * @param DeleteAvatar $command
     * @return \Bestkit\User\User
     * @throws \Bestkit\User\Exception\PermissionDeniedException
     */
    public function handle(DeleteAvatar $command)
    {
        $actor = $command->actor;

        $user = $this->users->findOrFail($command->userId);

        if ($actor->id !== $user->id) {
            $actor->assertCan('edit', $user);
        }

        $this->events->dispatch(
            new AvatarDeleting($user, $actor)
        );

        $this->uploader->remove($user);

        $user->save();

        $this->dispatchEventsFor($user, $actor);

        return $user;
    }
}
