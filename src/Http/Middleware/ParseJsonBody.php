<?php

namespace Bestkit\Http\Middleware;

use Illuminate\Support\Str;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface as Middleware;
use Psr\Http\Server\RequestHandlerInterface as Handler;

class ParseJsonBody implements Middleware
{
    public function process(Request $request, Handler $handler): Response
    {
        if (Str::contains($request->getHeaderLine('content-type'), 'json')) {
            $input = json_decode($request->getBody(), true);

            $request = $request->withParsedBody($input ?: []);
        }

        return $handler->handle($request);
    }
}
