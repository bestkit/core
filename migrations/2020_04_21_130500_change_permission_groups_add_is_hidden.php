<?php

use Bestkit\Database\Migration;

return Migration::addColumns('groups', [
    'is_hidden' => ['boolean', 'default' => false]
]);
