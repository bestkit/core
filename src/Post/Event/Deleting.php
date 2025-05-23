<?php

namespace Bestkit\Post\Event;

use Bestkit\Post\Post;
use Bestkit\User\User;

class Deleting
{
    /**
     * The post that is going to be deleted.
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
     * Any user input associated with the command.
     *
     * @var array
     */
    public $data;

    /**
     * @param \Bestkit\Post\Post $post
     * @param User $actor
     * @param array $data
     */
    public function __construct(Post $post, User $actor, array $data)
    {
        $this->post = $post;
        $this->actor = $actor;
        $this->data = $data;
    }
}
