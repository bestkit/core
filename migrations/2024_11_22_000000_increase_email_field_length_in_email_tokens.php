<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\Builder;

return [
    'up' => function (Builder $schema) {
        $schema->table('email_tokens', function (Blueprint $table) {
            $table->string('email', 254)->change();
        });
    },

    'down' => function (Builder $schema) {
        $schema->table('email_tokens', function (Blueprint $table) {
            $table->string('email', 150)->change();
        });
    }
];
