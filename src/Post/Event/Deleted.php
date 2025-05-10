<?php

namespace Bestkit\Post\Event;

use Bestkit\Post\Post;
use Bestkit\User\User;

class Deleted
{
    /**
     * @var \Bestkit\Post\Post
     */
    public $post;

    /**
     * @var User
     */
    public $actor;

    /**
     * @param \Bestkit\Post\Post $post
     */
    public function __construct(Post $post, User $actor = null)
    {
        $this->post = $post;
        $this->actor = $actor;
    }
}
