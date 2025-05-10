<?php

namespace Bestkit\Extension\Command;

use Bestkit\Extension\ExtensionManager;

class ToggleExtensionHandler
{
    /**
     * @var ExtensionManager
     */
    protected $extensions;

    public function __construct(ExtensionManager $extensions)
    {
        $this->extensions = $extensions;
    }

    /**
     * @throws \Bestkit\User\Exception\PermissionDeniedException
     * @throws \Bestkit\Extension\Exception\MissingDependenciesException
     * @throws \Bestkit\Extension\Exception\DependentExtensionsException
     */
    public function handle(ToggleExtension $command)
    {
        $command->actor->assertAdmin();

        if ($command->enabled) {
            $this->extensions->enable($command->name);
        } else {
            $this->extensions->disable($command->name);
        }
    }
}
