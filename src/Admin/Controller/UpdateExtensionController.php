<?php

namespace Bestkit\Admin\Controller;

use Bestkit\Bus\Dispatcher;
use Bestkit\Extension\Command\ToggleExtension;
use Bestkit\Http\RequestUtil;
use Bestkit\Http\UrlGenerator;
use Illuminate\Support\Arr;
use Laminas\Diactoros\Response\RedirectResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface;

class UpdateExtensionController implements RequestHandlerInterface
{
    /**
     * @var UrlGenerator
     */
    protected $url;

    /**
     * @var Dispatcher
     */
    protected $bus;

    public function __construct(UrlGenerator $url, Dispatcher $bus)
    {
        $this->url = $url;
        $this->bus = $bus;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(Request $request): ResponseInterface
    {
        $actor = RequestUtil::getActor($request);
        $enabled = (bool) (int) Arr::get($request->getParsedBody(), 'enabled');
        $name = Arr::get($request->getQueryParams(), 'name');

        $this->bus->dispatch(
            new ToggleExtension($actor, $name, $enabled)
        );

        return new RedirectResponse($this->url->to('admin')->base());
    }
}
