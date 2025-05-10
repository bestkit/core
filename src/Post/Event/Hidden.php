<?php

namespace Bestkit\Post\Event;

use Bestkit\Post\CommentPost;
use Bestkit\User\User;

class Hidden
{
    /**
     * @var CommentPost
     */
    public $post;

    /**
     * @var User
     */
    public $actor;

    /**
     * @param CommentPost $post
     */
    public function __construct(CommentPost $post, User $actor = null)
    {
        $this->post = $post;
        $this->actor = $actor;
    }
}
