<?php

namespace Bestkit\User\Filter;

use Bestkit\Filter\AbstractFilterer;
use Bestkit\User\User;
use Bestkit\User\UserRepository;
use Illuminate\Database\Eloquent\Builder;

class UserFilterer extends AbstractFilterer
{
    /**
     * @var UserRepository
     */
    protected $users;

    /**
     * @param UserRepository $users
     * @param array $filters
     * @param array $filterMutators
     */
    public function __construct(UserRepository $users, array $filters, array $filterMutators)
    {
        parent::__construct($filters, $filterMutators);

        $this->users = $users;
    }

    protected function getQuery(User $actor): Builder
    {
        return $this->users->query()->whereVisibleTo($actor);
    }
}
