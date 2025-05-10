<?php

namespace Bestkit\Install;

use Bestkit\Foundation\AbstractServiceProvider;
use Bestkit\Http\RouteCollection;
use Bestkit\Http\RouteHandlerFactory;
use Illuminate\Contracts\Container\Container;

class InstallServiceProvider extends AbstractServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function register()
    {
        $this->container->singleton('bestkit.install.routes', function () {
            return new RouteCollection;
        });
    }

    /**
     * {@inheritdoc}
     */
    public function boot(Container $container, RouteHandlerFactory $route)
    {
        $this->loadViewsFrom(__DIR__.'/../../views/install', 'bestkit.install');

        $this->populateRoutes($container->make('bestkit.install.routes'), $route);
    }

    /**
     * @param RouteCollection     $routes
     * @param RouteHandlerFactory $route
     */
    protected function populateRoutes(RouteCollection $routes, RouteHandlerFactory $route)
    {
        $routes->get(
            '/{path:.*}',
            'index',
            $route->toController(Controller\IndexController::class)
        );

        $routes->post(
            '/{path:.*}',
            'install',
            $route->toController(Controller\InstallController::class)
        );
    }
}
