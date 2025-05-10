<?php

namespace Bestkit\Post\Event;

use Bestkit\Post\CommentPost;
use Bestkit\User\User;

class Restored
{
    /**
     * @var \Bestkit\Post\CommentPost
     */
    public $post;

    /**
     * @var User
     */
    public $actor;

    /**
     * @param \Bestkit\Post\CommentPost $post
     */
    public function __construct(CommentPost $post, User $actor = null)
    {
        $this->post = $post;
        $this->actor = $actor;
    }
}
