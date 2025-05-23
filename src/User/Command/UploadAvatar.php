<?php

namespace Bestkit\User\Command;

use Bestkit\User\User;
use Psr\Http\Message\UploadedFileInterface;

class UploadAvatar
{
    /**
     * The ID of the user to upload the avatar for.
     *
     * @var int
     */
    public $userId;

    /**
     * The avatar file to upload.
     *
     * @var UploadedFileInterface
     */
    public $file;

    /**
     * The user performing the action.
     *
     * @var User
     */
    public $actor;

    /**
     * @param int $userId The ID of the user to upload the avatar for.
     * @param UploadedFileInterface $file The avatar file to upload.
     * @param User $actor The user performing the action.
     */
    public function __construct($userId, UploadedFileInterface $file, User $actor)
    {
        $this->userId = $userId;
        $this->file = $file;
        $this->actor = $actor;
    }
}
