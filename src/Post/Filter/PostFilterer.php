<?php

namespace Bestkit\Post\Filter;

use Bestkit\Filter\AbstractFilterer;
use Bestkit\Post\PostRepository;
use Bestkit\User\User;
use Illuminate\Database\Eloquent\Builder;

class PostFilterer extends AbstractFilterer
{
    /**
     * @var PostRepository
     */
    protected $posts;

    /**
     * @param PostRepository $posts
     * @param array $filters
     * @param array $filterMutators
     */
    public function __construct(PostRepository $posts, array $filters, array $filterMutators)
    {
        parent::__construct($filters, $filterMutators);

        $this->posts = $posts;
    }

    protected function getQuery(User $actor): Builder
    {
        return $this->posts->query()->select('posts.*')->whereVisibleTo($actor);
    }
}
