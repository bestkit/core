<?php

namespace Bestkit\Notification\Blueprint;

use Bestkit\Discussion\Discussion;
use Bestkit\Post\DiscussionRenamedPost;

class DiscussionRenamedBlueprint implements BlueprintInterface
{
    /**
     * @var \Bestkit\Post\DiscussionRenamedPost
     */
    protected $post;

    /**
     * @param DiscussionRenamedPost $post
     */
    public function __construct(DiscussionRenamedPost $post)
    {
        $this->post = $post;
    }

    /**
     * {@inheritdoc}
     */
    public function getFromUser()
    {
        return $this->post->user;
    }

    /**
     * {@inheritdoc}
     */
    public function getSubject()
    {
        return $this->post->discussion;
    }

    /**
     * {@inheritdoc}
     */
    public function getData()
    {
        return ['postNumber' => (int) $this->post->number];
    }

    /**
     * {@inheritdoc}
     */
    public static function getType()
    {
        return 'discussionRenamed';
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubjectModel()
    {
        return Discussion::class;
    }
}
