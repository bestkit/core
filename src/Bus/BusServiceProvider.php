<?php

namespace Bestkit\Bus;

use Bestkit\Foundation\AbstractServiceProvider;
use Illuminate\Bus\Dispatcher as BaseDispatcher;
use Illuminate\Contracts\Bus\Dispatcher as DispatcherContract;
use Illuminate\Contracts\Bus\QueueingDispatcher as QueueingDispatcherContract;
use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Queue\Factory as QueueFactoryContract;

class BusServiceProvider extends AbstractServiceProvider
{
    public function register()
    {
        $this->container->bind(BaseDispatcher::class, function (Container $container) {
            return new Dispatcher($container, function ($connection = null) use ($container) {
                return $container[QueueFactoryContract::class]->connection($connection);
            });
        });

        $this->container->alias(
            BaseDispatcher::class,
            DispatcherContract::class
        );

        $this->container->alias(
            BaseDispatcher::class,
            QueueingDispatcherContract::class
        );
    }
}
