<?php

use Bestkit\Database\Migration;

return Migration::renameTable('auth_tokens', 'registration_tokens');
