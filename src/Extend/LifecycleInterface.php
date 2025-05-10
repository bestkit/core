<?php

namespace Bestkit\Extend;

use Bestkit\Extension\Extension;
use Illuminate\Contracts\Container\Container;

interface LifecycleInterface
{
    public function onEnable(Container $container, Extension $extension);

    public function onDisable(Container $container, Extension $extension);
}
