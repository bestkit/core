<?php

namespace Bestkit\Notification;

use Bestkit\Foundation\AbstractServiceProvider;
use Bestkit\Notification\Blueprint\DiscussionRenamedBlueprint;
use Illuminate\Contracts\Container\Container;

class NotificationServiceProvider extends AbstractServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function register()
    {
        $this->container->singleton('bestkit.notification.drivers', function () {
            return [
                'alert' => Driver\AlertNotificationDriver::class,
                'email' => Driver\EmailNotificationDriver::class,
            ];
        });

        $this->container->singleton('bestkit.notification.blueprints', function () {
            return [
                DiscussionRenamedBlueprint::class => ['alert']
            ];
        });
    }

    /**
     * {@inheritdoc}
     */
    public function boot(Container $container)
    {
        $this->setNotificationDrivers($container);
        $this->setNotificationTypes($container);
    }

    /**
     * Register notification drivers.
     */
    protected function setNotificationDrivers(Container $container)
    {
        foreach ($container->make('bestkit.notification.drivers') as $driverName => $driver) {
            NotificationSyncer::addNotificationDriver($driverName, $container->make($driver));
        }
    }

    /**
     * Register notification types.
     */
    protected function setNotificationTypes(Container $container)
    {
        $blueprints = $container->make('bestkit.notification.blueprints');

        foreach ($blueprints as $blueprint => $driversEnabledByDefault) {
            $this->addType($blueprint, $driversEnabledByDefault);
        }
    }

    protected function addType(string $blueprint, array $driversEnabledByDefault)
    {
        Notification::setSubjectModel(
            $type = $blueprint::getType(),
            $blueprint::getSubjectModel()
        );

        foreach (NotificationSyncer::getNotificationDrivers() as $driverName => $driver) {
            $driver->registerType(
                $blueprint,
                $driversEnabledByDefault
            );
        }
    }
}
