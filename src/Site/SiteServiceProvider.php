<?php

namespace Bestkit\Site;

use Bestkit\Extension\Event\Disabled;
use Bestkit\Extension\Event\Enabled;
use Bestkit\Formatter\Formatter;
use Bestkit\Foundation\AbstractServiceProvider;
use Bestkit\Foundation\ErrorHandling\Registry;
use Bestkit\Foundation\ErrorHandling\Reporter;
use Bestkit\Foundation\ErrorHandling\ViewFormatter;
use Bestkit\Foundation\ErrorHandling\WhoopsFormatter;
use Bestkit\Foundation\Event\ClearingCache;
use Bestkit\Frontend\AddLocaleAssets;
use Bestkit\Frontend\AddTranslations;
use Bestkit\Frontend\Assets;
use Bestkit\Frontend\Compiler\Source\SourceCollector;
use Bestkit\Frontend\RecompileFrontendAssets;
use Bestkit\Http\Middleware as HttpMiddleware;
use Bestkit\Http\RouteCollection;
use Bestkit\Http\RouteHandlerFactory;
use Bestkit\Http\UrlGenerator;
use Bestkit\Locale\LocaleManager;
use Bestkit\Settings\Event\Saved;
use Bestkit\Settings\Event\Saving;
use Bestkit\Settings\SettingsRepositoryInterface;
use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\View\Factory;
use Laminas\Stratigility\MiddlewarePipe;
use Symfony\Contracts\Translation\TranslatorInterface;

class SiteServiceProvider extends AbstractServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function register()
    {
        $this->container->extend(UrlGenerator::class, function (UrlGenerator $url, Container $container) {
            return $url->addCollection('site', $container->make('bestkit.site.routes'));
        });

        $this->container->singleton('bestkit.site.routes', function (Container $container) {
            $routes = new RouteCollection;
            $this->populateRoutes($routes, $container);

            return $routes;
        });

        $this->container->afterResolving('bestkit.site.routes', function (RouteCollection $routes, Container $container) {
            $this->setDefaultRoute($routes, $container);
        });

        $this->container->singleton('bestkit.site.middleware', function () {
            return [
                HttpMiddleware\InjectActorReference::class,
                'bestkit.site.error_handler',
                HttpMiddleware\ParseJsonBody::class,
                HttpMiddleware\CollectGarbage::class,
                HttpMiddleware\StartSession::class,
                HttpMiddleware\RememberFromCookie::class,
                HttpMiddleware\AuthenticateWithSession::class,
                HttpMiddleware\SetLocale::class,
                'bestkit.site.route_resolver',
                HttpMiddleware\CheckCsrfToken::class,
                HttpMiddleware\ShareErrorsFromSession::class,
                HttpMiddleware\BestkitPromotionHeader::class,
                HttpMiddleware\ReferrerPolicyHeader::class,
                HttpMiddleware\ContentTypeOptionsHeader::class
            ];
        });

        $this->container->bind('bestkit.site.error_handler', function (Container $container) {
            return new HttpMiddleware\HandleErrors(
                $container->make(Registry::class),
                $container['bestkit.config']->inDebugMode() ? $container->make(WhoopsFormatter::class) : $container->make(ViewFormatter::class),
                $container->tagged(Reporter::class)
            );
        });

        $this->container->bind('bestkit.site.route_resolver', function (Container $container) {
            return new HttpMiddleware\ResolveRoute($container->make('bestkit.site.routes'));
        });

        $this->container->singleton('bestkit.site.handler', function (Container $container) {
            $pipe = new MiddlewarePipe;

            foreach ($container->make('bestkit.site.middleware') as $middleware) {
                $pipe->pipe($container->make($middleware));
            }

            $pipe->pipe(new HttpMiddleware\ExecuteRoute());

            return $pipe;
        });

        $this->container->bind('bestkit.assets.site', function (Container $container) {
            /** @var Assets $assets */
            $assets = $container->make('bestkit.assets.factory')('site');

            $assets->js(function (SourceCollector $sources) use ($container) {
                $sources->addFile(__DIR__.'/../../js/dist/site.js');
                $sources->addString(function () use ($container) {
                    return $container->make(Formatter::class)->getJs();
                });
            });

            $assets->css(function (SourceCollector $sources) use ($container) {
                $sources->addFile(__DIR__.'/../../less/site.less');
                $sources->addString(function () use ($container) {
                    return $container->make(SettingsRepositoryInterface::class)->get('custom_less', '');
                });
            });

            $container->make(AddTranslations::class)->forFrontend('site')->to($assets);
            $container->make(AddLocaleAssets::class)->to($assets);

            return $assets;
        });

        $this->container->bind('bestkit.frontend.site', function (Container $container) {
            return $container->make('bestkit.frontend.factory')('site');
        });

        $this->container->singleton('bestkit.site.discussions.sortmap', function () {
            return [
                'latest' => '-lastPostedAt',
                'top' => '-commentCount',
                'newest' => '-createdAt',
                'oldest' => 'createdAt'
            ];
        });
    }

    public function boot(Container $container, Dispatcher $events, Factory $view)
    {
        $this->loadViewsFrom(__DIR__.'/../../views', 'bestkit.site');

        $view->share([
            'translator' => $container->make(TranslatorInterface::class),
            'settings' => $container->make(SettingsRepositoryInterface::class)
        ]);

        $events->listen(
            [Enabled::class, Disabled::class, ClearingCache::class],
            function () use ($container) {
                $recompile = new RecompileFrontendAssets(
                    $container->make('bestkit.assets.site'),
                    $container->make(LocaleManager::class)
                );
                $recompile->flush();
            }
        );

        $events->listen(
            Saved::class,
            function (Saved $event) use ($container) {
                $recompile = new RecompileFrontendAssets(
                    $container->make('bestkit.assets.site'),
                    $container->make(LocaleManager::class)
                );
                $recompile->whenSettingsSaved($event);

                $validator = new ValidateCustomLess(
                    $container->make('bestkit.assets.site'),
                    $container->make('bestkit.locales'),
                    $container,
                    $container->make('bestkit.less.config')
                );
                $validator->whenSettingsSaved($event);
            }
        );

        $events->listen(
            Saving::class,
            function (Saving $event) use ($container) {
                $validator = new ValidateCustomLess(
                    $container->make('bestkit.assets.site'),
                    $container->make('bestkit.locales'),
                    $container,
                    $container->make('bestkit.less.config')
                );
                $validator->whenSettingsSaving($event);
            }
        );
    }

    /**
     * Populate the site client routes.
     *
     * @param RouteCollection $routes
     * @param Container       $container
     */
    protected function populateRoutes(RouteCollection $routes, Container $container)
    {
        $factory = $container->make(RouteHandlerFactory::class);

        $callback = include __DIR__.'/routes.php';
        $callback($routes, $factory);
    }

    /**
     * Determine the default route.
     *
     * @param RouteCollection $routes
     * @param Container       $container
     */
    protected function setDefaultRoute(RouteCollection $routes, Container $container)
    {
        $factory = $container->make(RouteHandlerFactory::class);
        $defaultRoute = $container->make('bestkit.settings')->get('default_route');

        if (isset($routes->getRouteData()[0]['GET'][$defaultRoute]['handler'])) {
            $toDefaultController = $routes->getRouteData()[0]['GET'][$defaultRoute]['handler'];
        } else {
            $toDefaultController = $factory->toSite(Content\Index::class);
        }

        $routes->get(
            '/',
            'default',
            $toDefaultController
        );
    }
}
