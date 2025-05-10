<?php

namespace Bestkit\Install\Console;

use Bestkit\Install\Installation;

interface DataProviderInterface
{
    public function configure(Installation $installation): Installation;
}
