<?php

namespace Bestkit\Queue;

use Illuminate\Queue\ListenerOptions;

class Listener extends \Illuminate\Queue\Listener
{
    protected function addEnvironment($command, ListenerOptions $options)
    {
        $options->environment = null;

        return $command;
    }

    protected function artisanBinary()
    {
        return 'bestkit';
    }
}
