<?php

use Bestkit\Database\Migration;

return Migration::renameColumn('email_tokens', 'id', 'token');
