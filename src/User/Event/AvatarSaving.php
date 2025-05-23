<?php

namespace Bestkit\User\Event;

use Bestkit\User\User;
use Intervention\Image\Image;

class AvatarSaving
{
    /**
     * The user whose avatar will be saved.
     *
     * @var User
     */
    public $user;

    /**
     * The user performing the action.
     *
     * @var User
     */
    public $actor;

    /**
     * The image that will be saved.
     *
     * @var Image
     */
    public $image;

    /**
     * @param User $user The user whose avatar will be saved.
     * @param User $actor The user performing the action.
     * @param Image $image The image that will be saved.
     */
    public function __construct(User $user, User $actor, Image $image)
    {
        $this->user = $user;
        $this->actor = $actor;
        $this->image = $image;
    }
}
