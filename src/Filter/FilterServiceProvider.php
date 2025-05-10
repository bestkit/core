<?php

namespace Bestkit\Filter;

use Bestkit\Discussion\Filter\DiscussionFilterer;
use Bestkit\Discussion\Query as DiscussionQuery;
use Bestkit\Foundation\AbstractServiceProvider;
use Bestkit\Foundation\ContainerUtil;
use Bestkit\Group\Filter as GroupFilter;
use Bestkit\Group\Filter\GroupFilterer;
use Bestkit\Http\Filter\AccessTokenFilterer;
use Bestkit\Http\Filter as HttpFilter;
use Bestkit\Post\Filter as PostFilter;
use Bestkit\Post\Filter\PostFilterer;
use Bestkit\User\Filter\UserFilterer;
use Bestkit\User\Query as UserQuery;
use Illuminate\Contracts\Container\Container;
use Illuminate\Support\Arr;

class FilterServiceProvider extends AbstractServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->container->singleton('bestkit.filter.filters', function () {
            return [
                AccessTokenFilterer::class => [
                    HttpFilter\UserFilter::class,
                ],
                DiscussionFilterer::class => [
                    DiscussionQuery\AuthorFilterGambit::class,
                    DiscussionQuery\CreatedFilterGambit::class,
                    DiscussionQuery\HiddenFilterGambit::class,
                    DiscussionQuery\UnreadFilterGambit::class,
                ],
                UserFilterer::class => [
                    UserQuery\EmailFilterGambit::class,
                    UserQuery\GroupFilterGambit::class,
                ],
                GroupFilterer::class => [
                    GroupFilter\HiddenFilter::class,
                ],
                PostFilterer::class => [
                    PostFilter\AuthorFilter::class,
                    PostFilter\DiscussionFilter::class,
                    PostFilter\IdFilter::class,
                    PostFilter\NumberFilter::class,
                    PostFilter\TypeFilter::class
                ],
            ];
        });

        $this->container->singleton('bestkit.filter.filter_mutators', function () {
            return [];
        });
    }

    public function boot(Container $container)
    {
        // We can resolve the filter mutators in the when->needs->give callback,
        // but we need to resolve at least one regardless so we know which
        // filterers we need to register filters for.
        $filters = $this->container->make('bestkit.filter.filters');

        foreach ($filters as $filterer => $filterClasses) {
            $container
                ->when($filterer)
                ->needs('$filters')
                ->give(function () use ($filterClasses) {
                    $compiled = [];

                    foreach ($filterClasses as $filterClass) {
                        $filter = $this->container->make($filterClass);
                        $compiled[$filter->getFilterKey()][] = $filter;
                    }

                    return $compiled;
                });

            $container
                ->when($filterer)
                ->needs('$filterMutators')
                ->give(function () use ($container, $filterer) {
                    return array_map(function ($filterMutatorClass) {
                        return ContainerUtil::wrapCallback($filterMutatorClass, $this->container);
                    }, Arr::get($container->make('bestkit.filter.filter_mutators'), $filterer, []));
                });
        }
    }
}
