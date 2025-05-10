<?php

namespace Bestkit\Install;

interface ReversibleStep extends Step
{
    public function revert();
}
