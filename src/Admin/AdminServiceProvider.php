<?php

namespace Bestkit\Admin;

use Bestkit\Extension\Event\Disabled;
use Bestkit\Extension\Event\Enabled;
use Bestkit\Foundation\AbstractServiceProvider;
use Bestkit\Foundation\ErrorHandling\Registry;
use Bestkit\Foundation\ErrorHandling\Reporter;
use Bestkit\Foundation\ErrorHandling\ViewFormatter;
use Bestkit\Foundation\ErrorHandling\WhoopsFormatter;
use Bestkit\Foundation\Event\ClearingCache;
use Bestkit\Frontend\AddLocaleAssets;
use Bestkit\Frontend\AddTranslations;
use Bestkit\Frontend\Compiler\Source\SourceCollector;
use Bestkit\Frontend\RecompileFrontendAssets;
use Bestkit\Http\Middleware as HttpMiddleware;
use Bestkit\Http\RouteCollection;
use Bestkit\Http\RouteHandlerFactory;
use Bestkit\Http\UrlGenerator;
use Bestkit\Locale\LocaleManager;
use Bestkit\Settings\Event\Saved;
use Illuminate\Contracts\Container\Container;
use Laminas\Stratigility\MiddlewarePipe;

class AdminServiceProvider extends AbstractServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function register()
    {
        $this->container->extend(UrlGenerator::class, function (UrlGenerator $url, Container $container) {
            return $url->addCollection('admin', $container->make('bestkit.admin.routes'), 'admin');
        });

        $this->container->singleton('bestkit.admin.routes', function () {
            $routes = new RouteCollection;
            $this->populateRoutes($routes);

            return $routes;
        });

        $this->container->singleton('bestkit.admin.middleware', function () {
            return [
                HttpMiddleware\InjectActorReference::class,
                'bestkit.admin.error_handler',
                HttpMiddleware\ParseJsonBody::class,
                HttpMiddleware\StartSession::class,
                HttpMiddleware\RememberFromCookie::class,
                HttpMiddleware\AuthenticateWithSession::class,
                HttpMiddleware\SetLocale::class,
                'bestkit.admin.route_resolver',
                HttpMiddleware\CheckCsrfToken::class,
                Middleware\RequireAdministrateAbility::class,
                HttpMiddleware\ReferrerPolicyHeader::class,
                HttpMiddleware\ContentTypeOptionsHeader::class,
                Middleware\DisableBrowserCache::class,
            ];
        });

        $this->container->bind('bestkit.admin.error_handler', function (Container $container) {
            return new HttpMiddleware\HandleErrors(
                $container->make(Registry::class),
                $container['bestkit.config']->inDebugMode() ? $container->make(WhoopsFormatter::class) : $container->make(ViewFormatter::class),
                $container->tagged(Reporter::class)
            );
        });

        $this->container->bind('bestkit.admin.route_resolver', function (Container $container) {
            return new HttpMiddleware\ResolveRoute($container->make('bestkit.admin.routes'));
        });

        $this->container->singleton('bestkit.admin.handler', function (Container $container) {
            $pipe = new MiddlewarePipe;

            foreach ($container->make('bestkit.admin.middleware') as $middleware) {
                $pipe->pipe($container->make($middleware));
            }

            $pipe->pipe(new HttpMiddleware\ExecuteRoute());

            return $pipe;
        });

        $this->container->bind('bestkit.assets.admin', function (Container $container) {
            /** @var \Bestkit\Frontend\Assets $assets */
            $assets = $container->make('bestkit.assets.factory')('admin');

            $assets->js(function (SourceCollector $sources) {
                $sources->addFile(__DIR__.'/../../js/dist/admin.js');
            });

            $assets->css(function (SourceCollector $sources) {
                $sources->addFile(__DIR__.'/../../less/admin.less');
            });

            $container->make(AddTranslations::class)->forFrontend('admin')->to($assets);
            $container->make(AddLocaleAssets::class)->to($assets);

            return $assets;
        });

        $this->container->bind('bestkit.frontend.admin', function (Container $container) {
            /** @var \Bestkit\Frontend\Frontend $frontend */
            $frontend = $container->make('bestkit.frontend.factory')('admin');

            $frontend->content($container->make(Content\AdminPayload::class));

            return $frontend;
        });
    }

    /**
     * {@inheritdoc}
     */
    public function boot()
    {
        $this->loadViewsFrom(__DIR__.'/../../views', 'bestkit.admin');

        $events = $this->container->make('events');

        $events->listen(
            [Enabled::class, Disabled::class, ClearingCache::class],
            function () {
                $recompile = new RecompileFrontendAssets(
                    $this->container->make('bestkit.assets.admin'),
                    $this->container->make(LocaleManager::class)
                );
                $recompile->flush();
            }
        );

        $events->listen(
            Saved::class,
            function (Saved $event) {
                $recompile = new RecompileFrontendAssets(
                    $this->container->make('bestkit.assets.admin'),
                    $this->container->make(LocaleManager::class)
                );
                $recompile->whenSettingsSaved($event);
            }
        );
    }

    /**
     * @param RouteCollection $routes
     */
    protected function populateRoutes(RouteCollection $routes)
    {
        $factory = $this->container->make(RouteHandlerFactory::class);

        $callback = include __DIR__.'/routes.php';
        $callback($routes, $factory);
    }
}
