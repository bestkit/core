<?php

namespace Bestkit\Queue\Console;

use Bestkit\Foundation\Config;

class WorkCommand extends \Illuminate\Queue\Console\WorkCommand
{
    protected function downForMaintenance()
    {
        if ($this->option('force')) {
            return false;
        }

        /** @var Config $config */
        $config = $this->laravel->make(Config::class);

        return $config->inMaintenanceMode();
    }
}
