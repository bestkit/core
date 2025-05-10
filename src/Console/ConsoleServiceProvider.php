<?php

namespace Bestkit\Console;

use Bestkit\Console\Cache\Factory;
use Bestkit\Database\Console\MigrateCommand;
use Bestkit\Database\Console\ResetCommand;
use Bestkit\Extension\Console\ToggleExtensionCommand;
use Bestkit\Foundation\AbstractServiceProvider;
use Bestkit\Foundation\Console\AssetsPublishCommand;
use Bestkit\Foundation\Console\CacheClearCommand;
use Bestkit\Foundation\Console\InfoCommand;
use Bestkit\Foundation\Console\ScheduleRunCommand;
use Illuminate\Console\Scheduling\CacheEventMutex;
use Illuminate\Console\Scheduling\CacheSchedulingMutex;
use Illuminate\Console\Scheduling\EventMutex;
use Illuminate\Console\Scheduling\Schedule as LaravelSchedule;
use Illuminate\Console\Scheduling\ScheduleListCommand;
use Illuminate\Console\Scheduling\SchedulingMutex;
use Illuminate\Contracts\Container\Container;

class ConsoleServiceProvider extends AbstractServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function register()
    {
        // Used by Laravel to proxy artisan commands to its binary.
        // Bestkit uses a similar binary, but it's called bestkit.
        if (! defined('ARTISAN_BINARY')) {
            define('ARTISAN_BINARY', 'bestkit');
        }

        // Bestkit doesn't fully use Laravel's cache system, but rather
        // creates and binds a single cache store.
        // See \Bestkit\Foundation\InstalledSite::registerCache
        // Since certain config options (e.g. withoutOverlapping, onOneServer)
        // need the cache, we must override the cache factory we give to the scheduling
        // mutexes so it returns our single custom cache.
        $this->container->bind(EventMutex::class, function ($container) {
            return new CacheEventMutex($container->make(Factory::class));
        });
        $this->container->bind(SchedulingMutex::class, function ($container) {
            return new CacheSchedulingMutex($container->make(Factory::class));
        });

        $this->container->singleton(LaravelSchedule::class, function (Container $container) {
            return $container->make(Schedule::class);
        });

        $this->container->singleton('bestkit.console.commands', function () {
            return [
                AssetsPublishCommand::class,
                CacheClearCommand::class,
                InfoCommand::class,
                MigrateCommand::class,
                ResetCommand::class,
                ScheduleListCommand::class,
                ScheduleRunCommand::class,
                ToggleExtensionCommand::class
                // Used internally to create DB dumps before major releases.
                // \Bestkit\Database\Console\GenerateDumpCommand::class
            ];
        });

        $this->container->singleton('bestkit.console.scheduled', function () {
            return [];
        });
    }

    /**
     * {@inheritDoc}
     */
    public function boot(Container $container)
    {
        $schedule = $container->make(LaravelSchedule::class);

        foreach ($container->make('bestkit.console.scheduled') as $scheduled) {
            $event = $schedule->command($scheduled['command'], $scheduled['args']);
            $scheduled['callback']($event);
        }
    }
}
