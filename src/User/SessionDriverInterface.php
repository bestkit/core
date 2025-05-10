<?php

namespace Bestkit\User;

use Bestkit\Foundation\Config;
use Bestkit\Settings\SettingsRepositoryInterface;
use SessionHandlerInterface;

interface SessionDriverInterface
{
    /**
     * Build a session handler to handle sessions.
     * Settings and configuration can either be pulled from the Bestkit settings repository
     * or the config.php file.
     *
     * @param SettingsRepositoryInterface $settings: An instance of the Bestkit settings repository.
     * @param Config $config: An instance of the wrapper class around `config.php`.
     */
    public function build(SettingsRepositoryInterface $settings, Config $config): SessionHandlerInterface;
}
