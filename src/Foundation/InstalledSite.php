<?php

namespace Bestkit\Foundation;

use Bestkit\Admin\AdminServiceProvider;
use Bestkit\Api\ApiServiceProvider;
use Bestkit\Bus\BusServiceProvider;
use Bestkit\Console\ConsoleServiceProvider;
use Bestkit\Database\DatabaseServiceProvider;
use Bestkit\Discussion\DiscussionServiceProvider;
use Bestkit\Extension\ExtensionServiceProvider;
use Bestkit\Filesystem\FilesystemServiceProvider;
use Bestkit\Filter\FilterServiceProvider;
use Bestkit\Formatter\FormatterServiceProvider;
use Bestkit\Site\SiteServiceProvider;
use Bestkit\Frontend\FrontendServiceProvider;
use Bestkit\Group\GroupServiceProvider;
use Bestkit\Http\HttpServiceProvider;
use Bestkit\Locale\LocaleServiceProvider;
use Bestkit\Mail\MailServiceProvider;
use Bestkit\Notification\NotificationServiceProvider;
use Bestkit\Post\PostServiceProvider;
use Bestkit\Queue\QueueServiceProvider;
use Bestkit\Search\SearchServiceProvider;
use Bestkit\Settings\SettingsServiceProvider;
use Bestkit\Update\UpdateServiceProvider;
use Bestkit\User\SessionServiceProvider;
use Bestkit\User\UserServiceProvider;
use Illuminate\Cache\FileStore;
use Illuminate\Cache\Repository as CacheRepository;
use Illuminate\Config\Repository as ConfigRepository;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Contracts\Cache\Store;
use Illuminate\Contracts\Container\Container;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Hashing\HashServiceProvider;
use Illuminate\Validation\ValidationServiceProvider;
use Illuminate\View\ViewServiceProvider;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;
use Psr\Log\LoggerInterface;

class InstalledSite implements SiteInterface
{
    /**
     * @var Paths
     */
    protected $paths;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var \Bestkit\Extend\ExtenderInterface[]
     */
    protected $extenders = [];

    public function __construct(Paths $paths, Config $config)
    {
        $this->paths = $paths;
        $this->config = $config;
    }

    /**
     * Create and boot a Bestkit application instance.
     *
     * @return InstalledApp
     */
    public function bootApp(): AppInterface
    {
        return new InstalledApp(
            $this->bootLaravel(),
            $this->config
        );
    }

    /**
     * @param \Bestkit\Extend\ExtenderInterface[] $extenders
     * @return InstalledSite
     */
    public function extendWith(array $extenders): self
    {
        $this->extenders = $extenders;

        return $this;
    }

    protected function bootLaravel(): Container
    {
        $container = new \Illuminate\Container\Container;
        $laravel = new Application($container, $this->paths);

        $container->instance('env', 'production');
        $container->instance('bestkit.config', $this->config);
        $container->alias('bestkit.config', Config::class);
        $container->instance('bestkit.debug', $this->config->inDebugMode());
        $container->instance('config', $config = $this->getIlluminateConfig());
        $container->instance('bestkit.maintenance.handler', new MaintenanceModeHandler);

        $this->registerLogger($container);
        $this->registerCache($container);

        $laravel->register(AdminServiceProvider::class);
        $laravel->register(ApiServiceProvider::class);
        $laravel->register(BusServiceProvider::class);
        $laravel->register(ConsoleServiceProvider::class);
        $laravel->register(DatabaseServiceProvider::class);
        $laravel->register(DiscussionServiceProvider::class);
        $laravel->register(ExtensionServiceProvider::class);
        $laravel->register(ErrorServiceProvider::class);
        $laravel->register(FilesystemServiceProvider::class);
        $laravel->register(FilterServiceProvider::class);
        $laravel->register(FormatterServiceProvider::class);
        $laravel->register(SiteServiceProvider::class);
        $laravel->register(FrontendServiceProvider::class);
        $laravel->register(GroupServiceProvider::class);
        $laravel->register(HashServiceProvider::class);
        $laravel->register(HttpServiceProvider::class);
        $laravel->register(LocaleServiceProvider::class);
        $laravel->register(MailServiceProvider::class);
        $laravel->register(NotificationServiceProvider::class);
        $laravel->register(PostServiceProvider::class);
        $laravel->register(QueueServiceProvider::class);
        $laravel->register(SearchServiceProvider::class);
        $laravel->register(SessionServiceProvider::class);
        $laravel->register(SettingsServiceProvider::class);
        $laravel->register(UpdateServiceProvider::class);
        $laravel->register(UserServiceProvider::class);
        $laravel->register(ValidationServiceProvider::class);
        $laravel->register(ViewServiceProvider::class);

        $laravel->booting(function () use ($container) {
            // Run all local-site extenders before booting service providers
            // (but after those from "real" extensions, which have been set up
            // in a service provider above).
            foreach ($this->extenders as $extension) {
                $extension->extend($container);
            }
        });

        $laravel->boot();

        return $container;
    }

    /**
     * @return ConfigRepository
     */
    protected function getIlluminateConfig()
    {
        return new ConfigRepository([
            'app' => [
                'timezone' => 'UTC'
            ],
            'view' => [
                'paths' => [],
                'compiled' => $this->paths->storage.'/views',
            ],
            'session' => [
                'lifetime' => 120,
                'files' => $this->paths->storage.'/sessions',
                'cookie' => 'session'
            ]
        ]);
    }

    protected function registerLogger(Container $container)
    {
        $logPath = $this->paths->storage.'/logs/bestkit.log';
        $logLevel = $this->config->inDebugMode() ? Logger::DEBUG : Logger::INFO;
        $handler = new RotatingFileHandler($logPath, 0, $logLevel);
        $handler->setFormatter(new LineFormatter(null, null, true, true));

        $container->instance('log', new Logger('bestkit', [$handler]));
        $container->alias('log', LoggerInterface::class);
    }

    protected function registerCache(Container $container)
    {
        $container->singleton('cache.store', function ($container) {
            return new CacheRepository($container->make('cache.filestore'));
        });
        $container->alias('cache.store', Repository::class);

        $container->singleton('cache.filestore', function () {
            return new FileStore(new Filesystem, $this->paths->storage.'/cache');
        });
        $container->alias('cache.filestore', Store::class);
    }
}
