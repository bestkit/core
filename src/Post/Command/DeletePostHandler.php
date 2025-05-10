<?php

namespace Bestkit\Post\Command;

use Bestkit\Foundation\DispatchEventsTrait;
use Bestkit\Post\Event\Deleting;
use Bestkit\Post\PostRepository;
use Illuminate\Contracts\Events\Dispatcher;

class DeletePostHandler
{
    use DispatchEventsTrait;

    /**
     * @var \Bestkit\Post\PostRepository
     */
    protected $posts;

    /**
     * @param Dispatcher $events
     * @param \Bestkit\Post\PostRepository $posts
     */
    public function __construct(Dispatcher $events, PostRepository $posts)
    {
        $this->events = $events;
        $this->posts = $posts;
    }

    /**
     * @param DeletePost $command
     * @return \Bestkit\Post\Post
     * @throws \Bestkit\User\Exception\PermissionDeniedException
     */
    public function handle(DeletePost $command)
    {
        $actor = $command->actor;

        $post = $this->posts->findOrFail($command->postId, $actor);

        $actor->assertCan('delete', $post);

        $this->events->dispatch(
            new Deleting($post, $actor, $command->data)
        );

        $post->delete();

        $this->dispatchEventsFor($post, $actor);

        return $post;
    }
}
