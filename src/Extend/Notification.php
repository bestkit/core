<?php

namespace Bestkit\Extend;

use Bestkit\Extension\Extension;
use Bestkit\Foundation\ContainerUtil;
use Bestkit\Notification\NotificationSyncer;
use Illuminate\Contracts\Container\Container;

class Notification implements ExtenderInterface
{
    private $blueprints = [];
    private $serializers = [];
    private $drivers = [];
    private $typesEnabledByDefault = [];
    private $beforeSendingCallbacks = [];

    /**
     * @param string $blueprint: The ::class attribute of the blueprint class.
     *                          This blueprint should implement \Bestkit\Notification\Blueprint\BlueprintInterface.
     * @param string $serializer: The ::class attribute of the serializer class.
     *                           This serializer should extend from \Bestkit\Api\Serializer\AbstractSerializer.
     * @param string[] $driversEnabledByDefault: The names of the drivers enabled by default for this notification type.
     *                                       (example: alert, email).
     * @return self
     */
    public function type(string $blueprint, string $serializer, array $driversEnabledByDefault = []): self
    {
        $this->blueprints[$blueprint] = $driversEnabledByDefault;
        $this->serializers[$blueprint::getType()] = $serializer;

        return $this;
    }

    /**
     * @param string $driverName: The name of the notification driver.
     * @param string $driver: The ::class attribute of the driver class.
     *                       This driver should implement \Bestkit\Notification\Driver\NotificationDriverInterface.
     * @param string[] $typesEnabledByDefault: The names of blueprint classes of types enabled by default for this driver.
     * @return self
     */
    public function driver(string $driverName, string $driver, array $typesEnabledByDefault = []): self
    {
        $this->drivers[$driverName] = $driver;
        $this->typesEnabledByDefault[$driverName] = $typesEnabledByDefault;

        return $this;
    }

    /**
     * @param callable|string $callback
     *
     * The callback can be a closure or an invokable class, and should accept:
     * - \Bestkit\Notification\Blueprint\BlueprintInterface $blueprint
     * - \Bestkit\User\User[] $newRecipients
     *
     * The callable should return an array of recipients.
     * - \Bestkit\User\User[] $newRecipients
     *
     * @return self
     */
    public function beforeSending($callback): self
    {
        $this->beforeSendingCallbacks[] = $callback;

        return $this;
    }

    public function extend(Container $container, Extension $extension = null)
    {
        $container->extend('bestkit.notification.blueprints', function ($existingBlueprints) {
            $existingBlueprints = array_merge($existingBlueprints, $this->blueprints);

            foreach ($this->typesEnabledByDefault as $driverName => $typesEnabledByDefault) {
                foreach ($typesEnabledByDefault as $blueprintClass) {
                    if (isset($existingBlueprints[$blueprintClass]) && (! in_array($driverName, $existingBlueprints[$blueprintClass]))) {
                        $existingBlueprints[$blueprintClass][] = $driverName;
                    }
                }
            }

            return $existingBlueprints;
        });

        $container->extend('bestkit.api.notification_serializers', function ($existingSerializers) {
            return array_merge($existingSerializers, $this->serializers);
        });

        $container->extend('bestkit.notification.drivers', function ($existingDrivers) {
            return array_merge($existingDrivers, $this->drivers);
        });

        foreach ($this->beforeSendingCallbacks as $callback) {
            NotificationSyncer::beforeSending(ContainerUtil::wrapCallback($callback, $container));
        }
    }
}
