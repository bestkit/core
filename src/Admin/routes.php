<?php

use Bestkit\Admin\Content\Index;
use Bestkit\Admin\Controller\UpdateExtensionController;
use Bestkit\Http\RouteCollection;
use Bestkit\Http\RouteHandlerFactory;

return function (RouteCollection $map, RouteHandlerFactory $route) {
    $map->get(
        '/',
        'index',
        $route->toAdmin(Index::class)
    );

    $map->post(
        '/extensions/{name}',
        'extensions.update',
        $route->toController(UpdateExtensionController::class)
    );
};
