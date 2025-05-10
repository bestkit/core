<?php

namespace Bestkit\Foundation;

use Bestkit\Extension\Extension;
use Bestkit\Extension\ExtensionManager;

trait ExtensionIdTrait
{
    protected function getClassExtensionId(): ?string
    {
        $extensions = resolve(ExtensionManager::class);

        return $extensions->getExtensions()
                ->mapWithKeys(function (Extension $extension) {
                    return [$extension->getId() => $extension->getNamespace()];
                })
                ->filter(function ($namespace) {
                    return $namespace && str_starts_with(static::class, $namespace);
                })
                ->keys()
                ->first();
    }
}
