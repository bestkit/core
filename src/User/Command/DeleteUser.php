<?php

namespace Bestkit\User\Command;

use Bestkit\User\User;

class DeleteUser
{
    /**
     * The ID of the user to delete.
     *
     * @var int
     */
    public $userId;

    /**
     * The user performing the action.
     *
     * @var User
     */
    public $actor;

    /**
     * Any other user input associated with the action. This is unused by
     * default, but may be used by extensions.
     *
     * @var array
     */
    public $data;

    /**
     * @param int $userId The ID of the user to delete.
     * @param User $actor The user performing the action.
     * @param array $data Any other user input associated with the action. This
     *     is unused by default, but may be used by extensions.
     */
    public function __construct($userId, User $actor, array $data = [])
    {
        $this->userId = $userId;
        $this->actor = $actor;
        $this->data = $data;
    }
}
