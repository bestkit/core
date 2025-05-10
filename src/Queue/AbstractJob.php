<?php

namespace Bestkit\Queue;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class AbstractJob implements ShouldQueue
{
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * The name of the queue on which the job should be placed.
     *
     * This is only effective on jobs that extend `\Bestkit\Queue\AbstractJob` and dispatched via Redis.
     *
     * @var string|null
     */
    public static $sendOnQueue = null;

    public function __construct()
    {
        if (static::$sendOnQueue) {
            $this->onQueue(static::$sendOnQueue);
        }
    }
}
