<?php

namespace Bestkit\Http;

use Bestkit\Discussion\Discussion;
use Bestkit\Discussion\IdWithTransliteratedSlugDriver;
use Bestkit\Discussion\Utf8SlugDriver;
use Bestkit\Foundation\AbstractServiceProvider;
use Bestkit\Http\Access\ScopeAccessTokenVisibility;
use Bestkit\Settings\SettingsRepositoryInterface;
use Bestkit\User\IdSlugDriver;
use Bestkit\User\User;
use Bestkit\User\UsernameSlugDriver;
use Illuminate\Contracts\Container\Container;
use Illuminate\Support\Arr;

class HttpServiceProvider extends AbstractServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function register()
    {
        $this->container->singleton('bestkit.http.csrfExemptPaths', function () {
            return ['token'];
        });

        $this->container->bind(Middleware\CheckCsrfToken::class, function (Container $container) {
            return new Middleware\CheckCsrfToken($container->make('bestkit.http.csrfExemptPaths'));
        });

        $this->container->singleton('bestkit.http.slugDrivers', function () {
            return [
                Discussion::class => [
                    'default' => IdWithTransliteratedSlugDriver::class,
                    'utf8' => Utf8SlugDriver::class,
                ],
                User::class => [
                    'default' => UsernameSlugDriver::class,
                    'id' => IdSlugDriver::class
                ],
            ];
        });

        $this->container->singleton('bestkit.http.selectedSlugDrivers', function (Container $container) {
            $settings = $container->make(SettingsRepositoryInterface::class);

            $compiledDrivers = [];

            foreach ($container->make('bestkit.http.slugDrivers') as $resourceClass => $resourceDrivers) {
                $driverKey = $settings->get("slug_driver_$resourceClass", 'default');

                $driverClass = Arr::get($resourceDrivers, $driverKey, $resourceDrivers['default']);

                $compiledDrivers[$resourceClass] = $container->make($driverClass);
            }

            return $compiledDrivers;
        });
        $this->container->bind(SlugManager::class, function (Container $container) {
            return new SlugManager($container->make('bestkit.http.selectedSlugDrivers'));
        });
    }

    /**
     * {@inheritdoc}
     */
    public function boot()
    {
        $this->setAccessTokenTypes();

        AccessToken::registerVisibilityScoper(new ScopeAccessTokenVisibility(), 'view');
    }

    protected function setAccessTokenTypes()
    {
        $models = [
            DeveloperAccessToken::class,
            RememberAccessToken::class,
            SessionAccessToken::class
        ];

        foreach ($models as $model) {
            AccessToken::setModel($model::$type, $model);
        }
    }
}
