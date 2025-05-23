<?php

namespace Bestkit\Http\Middleware;

use Bestkit\Http\RequestUtil;
use Bestkit\User\Guest;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface as Middleware;
use Psr\Http\Server\RequestHandlerInterface as Handler;

class InjectActorReference implements Middleware
{
    public function process(Request $request, Handler $handler): Response
    {
        $request = RequestUtil::withActor($request, new Guest);

        return $handler->handle($request);
    }
}
