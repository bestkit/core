<?php

use Bestkit\Database\Migration;

return Migration::renameColumn('registration_tokens', 'id', 'token');
