<?php

namespace Bestkit\Admin\Content;

use Bestkit\Extension\ExtensionManager;
use Bestkit\Foundation\ApplicationInfoProvider;
use Bestkit\Foundation\Config;
use Bestkit\Frontend\Document;
use Bestkit\Group\Permission;
use Bestkit\Settings\Event\Deserializing;
use Bestkit\Settings\SettingsRepositoryInterface;
use Bestkit\User\User;
use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Arr;
use Psr\Http\Message\ServerRequestInterface as Request;

class AdminPayload
{
    /**
     * @var Container;
     */
    protected $container;

    /**
     * @var SettingsRepositoryInterface
     */
    protected $settings;

    /**
     * @var ExtensionManager
     */
    protected $extensions;

    /**
     * @var ConnectionInterface
     */
    protected $db;

    /**
     * @var Dispatcher
     */
    protected $events;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var ApplicationInfoProvider
     */
    protected $appInfo;

    /**
     * @param Container $container
     * @param SettingsRepositoryInterface $settings
     * @param ExtensionManager $extensions
     * @param ConnectionInterface $db
     * @param Dispatcher $events
     * @param Config $config
     * @param ApplicationInfoProvider $appInfo
     */
    public function __construct(
        Container $container,
        SettingsRepositoryInterface $settings,
        ExtensionManager $extensions,
        ConnectionInterface $db,
        Dispatcher $events,
        Config $config,
        ApplicationInfoProvider $appInfo
    ) {
        $this->container = $container;
        $this->settings = $settings;
        $this->extensions = $extensions;
        $this->db = $db;
        $this->events = $events;
        $this->config = $config;
        $this->appInfo = $appInfo;
    }

    public function __invoke(Document $document, Request $request)
    {
        $settings = $this->settings->all();

        $this->events->dispatch(
            new Deserializing($settings)
        );

        $document->payload['settings'] = $settings;
        $document->payload['permissions'] = Permission::map();
        $document->payload['extensions'] = $this->extensions->getExtensions()->toArray();

        $document->payload['displayNameDrivers'] = array_keys($this->container->make('bestkit.user.display_name.supported_drivers'));
        $document->payload['slugDrivers'] = array_map(function ($resourceDrivers) {
            return array_keys($resourceDrivers);
        }, $this->container->make('bestkit.http.slugDrivers'));

        $document->payload['phpVersion'] = $this->appInfo->identifyPHPVersion();
        $document->payload['mysqlVersion'] = $this->appInfo->identifyDatabaseVersion();
        $document->payload['debugEnabled'] = Arr::get($this->config, 'debug');

        if ($this->appInfo->scheduledTasksRegistered()) {
            $document->payload['schedulerStatus'] = $this->appInfo->getSchedulerStatus();
        }

        $document->payload['queueDriver'] = $this->appInfo->identifyQueueDriver();
        $document->payload['sessionDriver'] = $this->appInfo->identifySessionDriver(true);

        /**
         * Used in the admin user list. Implemented as this as it matches the API in bestkit/statistics.
         * If bestkit/statistics ext is enabled, it will override this data with its own stats.
         *
         * This allows the front-end code to be simpler and use one single source of truth to pull the
         * total user count from.
         */
        $document->payload['modelStatistics'] = [
            'users' => [
                'total' => User::count()
            ]
        ];
    }
}
