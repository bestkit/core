<?php

namespace Bestkit\Discussion\Command;

use Bestkit\User\User;

class DeleteDiscussion
{
    /**
     * The ID of the discussion to delete.
     *
     * @var int
     */
    public $discussionId;

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
     * @param int $discussionId The ID of the discussion to delete.
     * @param User $actor The user performing the action.
     * @param array $data Any other user input associated with the action. This
     *     is unused by default, but may be used by extensions.
     */
    public function __construct($discussionId, User $actor, array $data = [])
    {
        $this->discussionId = $discussionId;
        $this->actor = $actor;
        $this->data = $data;
    }
}
