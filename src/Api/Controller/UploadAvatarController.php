<?php

namespace Bestkit\Api\Controller;

use Bestkit\Api\Serializer\UserSerializer;
use Bestkit\Http\RequestUtil;
use Bestkit\User\Command\UploadAvatar;
use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Support\Arr;
use Psr\Http\Message\ServerRequestInterface;
use Tobscure\JsonApi\Document;

class UploadAvatarController extends AbstractShowController
{
    /**
     * {@inheritdoc}
     */
    public $serializer = UserSerializer::class;

    /**
     * @var Dispatcher
     */
    protected $bus;

    /**
     * @param Dispatcher $bus
     */
    public function __construct(Dispatcher $bus)
    {
        $this->bus = $bus;
    }

    /**
     * {@inheritdoc}
     */
    protected function data(ServerRequestInterface $request, Document $document)
    {
        $id = Arr::get($request->getQueryParams(), 'id');
        $actor = RequestUtil::getActor($request);
        $file = Arr::get($request->getUploadedFiles(), 'avatar');

        return $this->bus->dispatch(
            new UploadAvatar($id, $file, $actor)
        );
    }
}
