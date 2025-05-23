<?php

namespace Bestkit\Discussion\Command;

use Exception;
use Bestkit\Discussion\Discussion;
use Bestkit\Discussion\DiscussionValidator;
use Bestkit\Discussion\Event\Saving;
use Bestkit\Foundation\DispatchEventsTrait;
use Bestkit\Post\Command\PostReply;
use Illuminate\Contracts\Bus\Dispatcher as BusDispatcher;
use Illuminate\Contracts\Events\Dispatcher as EventDispatcher;
use Illuminate\Support\Arr;

class StartDiscussionHandler
{
    use DispatchEventsTrait;

    /**
     * @var BusDispatcher
     */
    protected $bus;

    /**
     * @var \Bestkit\Discussion\DiscussionValidator
     */
    protected $validator;

    /**
     * @param EventDispatcher $events
     * @param BusDispatcher $bus
     * @param \Bestkit\Discussion\DiscussionValidator $validator
     */
    public function __construct(EventDispatcher $events, BusDispatcher $bus, DiscussionValidator $validator)
    {
        $this->events = $events;
        $this->bus = $bus;
        $this->validator = $validator;
    }

    /**
     * @param StartDiscussion $command
     * @return mixed
     * @throws Exception
     */
    public function handle(StartDiscussion $command)
    {
        $actor = $command->actor;
        $data = $command->data;
        $ipAddress = $command->ipAddress;

        $actor->assertCan('startDiscussion');

        // Create a new Discussion entity, persist it, and dispatch domain
        // events. Before persistence, though, fire an event to give plugins
        // an opportunity to alter the discussion entity based on data in the
        // command they may have passed through in the controller.
        $discussion = Discussion::start(
            Arr::get($data, 'attributes.title'),
            $actor
        );

        $this->events->dispatch(
            new Saving($discussion, $actor, $data)
        );

        $this->validator->assertValid($discussion->getAttributes());

        $discussion->save();

        // Now that the discussion has been created, we can add the first post.
        // We will do this by running the PostReply command.
        try {
            $post = $this->bus->dispatch(
                new PostReply($discussion->id, $actor, $data, $ipAddress, true)
            );
        } catch (Exception $e) {
            $discussion->delete();

            throw $e;
        }

        // Before we dispatch events, refresh our discussion instance's
        // attributes as posting the reply will have changed some of them (e.g.
        // last_time.)
        $discussion->setRawAttributes($post->discussion->getAttributes(), true);
        $discussion->setFirstPost($post);
        $discussion->setLastPost($post);

        $this->dispatchEventsFor($discussion, $actor);

        $discussion->save();

        return $discussion;
    }
}
