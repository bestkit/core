<?php

use Bestkit\Database\Migration;

return Migration::renameColumn('password_tokens', 'id', 'token');
