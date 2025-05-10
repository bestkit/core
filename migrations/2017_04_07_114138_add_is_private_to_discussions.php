<?php

use Bestkit\Database\Migration;

return Migration::addColumns('discussions', [
    'is_private' => ['boolean', 'default' => false]
]);
