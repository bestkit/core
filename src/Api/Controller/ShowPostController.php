<?php

namespace Bestkit\Api\Controller;

use Bestkit\Api\Serializer\PostSerializer;
use Bestkit\Http\RequestUtil;
use Bestkit\Post\PostRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;
use Psr\Http\Message\ServerRequestInterface;
use Tobscure\JsonApi\Document;

class ShowPostController extends AbstractShowController
{
    /**
     * {@inheritdoc}
     */
    public $serializer = PostSerializer::class;

    /**
     * {@inheritdoc}
     */
    public $include = [
        'user',
        'user.groups',
        'editedUser',
        'hiddenUser',
        'discussion'
    ];

    /**
     * @var \Bestkit\Post\PostRepository
     */
    protected $posts;

    /**
     * @param \Bestkit\Post\PostRepository $posts
     */
    public function __construct(PostRepository $posts)
    {
        $this->posts = $posts;
    }

    /**
     * {@inheritdoc}
     */
    protected function data(ServerRequestInterface $request, Document $document)
    {
        $post = $this->posts->findOrFail(Arr::get($request->getQueryParams(), 'id'), RequestUtil::getActor($request));

        $include = $this->extractInclude($request);

        $this->loadRelations(new Collection([$post]), $include, $request);

        return $post;
    }
}
