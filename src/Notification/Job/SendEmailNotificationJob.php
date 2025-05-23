<?php

namespace Bestkit\Notification\Job;

use Bestkit\Notification\MailableInterface;
use Bestkit\Notification\NotificationMailer;
use Bestkit\Queue\AbstractJob;
use Bestkit\User\User;

class SendEmailNotificationJob extends AbstractJob
{
    /**
     * @var MailableInterface
     */
    private $blueprint;

    /**
     * @var User
     */
    private $recipient;

    public function __construct(MailableInterface $blueprint, User $recipient)
    {
        parent::__construct();

        $this->blueprint = $blueprint;
        $this->recipient = $recipient;
    }

    public function handle(NotificationMailer $mailer)
    {
        $mailer->send($this->blueprint, $this->recipient);
    }
}
