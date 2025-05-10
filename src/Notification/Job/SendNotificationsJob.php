<?php

namespace Bestkit\Notification\Job;

use Bestkit\Notification\Blueprint\BlueprintInterface;
use Bestkit\Notification\Notification;
use Bestkit\Queue\AbstractJob;
use Bestkit\User\User;

class SendNotificationsJob extends AbstractJob
{
    /**
     * @var BlueprintInterface
     */
    private $blueprint;

    /**
     * @var User[]
     */
    private $recipients;

    public function __construct(BlueprintInterface $blueprint, array $recipients = [])
    {
        parent::__construct();

        $this->blueprint = $blueprint;
        $this->recipients = $recipients;
    }

    public function handle()
    {
        Notification::notify($this->recipients, $this->blueprint);
    }
}
