<?php

namespace Bestkit\Post\Event;

use Bestkit\Post\Post;
use Bestkit\User\User;

class Saving
{
    /**
     * The post that will be saved.
     *
     * @var \Bestkit\Post\Post
     */
    public $post;

    /**
     * The user who is performing the action.
     *
     * @var User
     */
    public $actor;

    /**
     * The attributes to update on the post.
     *
     * @var array
     */
    public $data;

    /**
     * @param \Bestkit\Post\Post $post
     * @param User $actor
     * @param array $data
     */
    public function __construct(Post $post, User $actor, array $data = [])
    {
        $this->post = $post;
        $this->actor = $actor;
        $this->data = $data;
    }
}
