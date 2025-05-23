<?php

namespace Bestkit\Discussion\Search;

use Bestkit\Discussion\DiscussionRepository;
use Bestkit\Search\AbstractSearcher;
use Bestkit\Search\GambitManager;
use Bestkit\User\User;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Database\Eloquent\Builder;

class DiscussionSearcher extends AbstractSearcher
{
    /**
     * @var DiscussionRepository
     */
    protected $discussions;

    /**
     * @var Dispatcher
     */
    protected $events;

    /**
     * @param DiscussionRepository $discussions
     * @param Dispatcher $events
     * @param GambitManager $gambits
     * @param array $searchMutators
     */
    public function __construct(DiscussionRepository $discussions, Dispatcher $events, GambitManager $gambits, array $searchMutators)
    {
        parent::__construct($gambits, $searchMutators);

        $this->discussions = $discussions;
        $this->events = $events;
    }

    protected function getQuery(User $actor): Builder
    {
        return $this->discussions->query()->select('discussions.*')->whereVisibleTo($actor);
    }
}
