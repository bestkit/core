<?php

namespace Bestkit\Queue;

use Bestkit\Extension\Event\Disabled;
use Bestkit\Extension\Event\Enabled;
use Bestkit\Foundation\Event\ClearingCache;
use Bestkit\Settings\Event\Saved;
use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Queue\Console\RestartCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;

class QueueRestarter
{
    /**
     * @var Container
     */
    protected $container;

    /**
     * @var RestartCommand
     */
    protected $command;

    public function __construct(Container $container, RestartCommand $command)
    {
        $this->container = $container;
        $this->command = $command;
    }

    public function subscribe(Dispatcher $events)
    {
        $events->listen([
            ClearingCache::class, Saved::class,
            Enabled::class, Disabled::class
        ], [$this, 'restart']);
    }

    public function restart()
    {
        $this->command->setLaravel($this->container);

        $this->command->run(
            new ArrayInput([]),
            new NullOutput
        );
    }
}
