<?php

namespace Bestkit\Mail;

use Illuminate\Mail\Transport\LogTransport;
use Swift_Mime_SimpleMessage;

class BestkitLogTransport extends LogTransport
{
    /**
     * {@inheritdoc}
     *
     * @return int
     */
    public function send(Swift_Mime_SimpleMessage $message, &$failedRecipients = null)
    {
        $this->beforeSendPerformed($message);

        // Overriden to use info, so the log driver works in non-debug mode.
        $this->logger->info($this->getMimeEntityString($message));

        $this->sendPerformed($message);

        return $this->numberOfRecipients($message);
    }
}
