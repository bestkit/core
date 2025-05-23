<?php

use Bestkit\Database\Migration;
use Illuminate\Database\Schema\Blueprint;

return Migration::createTable(
    'users',
    function (Blueprint $table) {
        $table->increments('id');
        $table->string('username', 100)->unique();
        $table->string('email', 150)->unique();
        $table->boolean('is_activated')->default(0);
        $table->string('password', 100);
        $table->string('avatar_path', 100)->nullable();
        $table->binary('preferences')->nullable();
        $table->dateTime('join_time')->nullable();
        $table->dateTime('last_seen_time')->nullable();
        $table->dateTime('read_time')->nullable();
        $table->dateTime('notification_read_time')->nullable();
        $table->integer('discussions_count')->unsigned()->default(0);
        $table->integer('comments_count')->unsigned()->default(0);
    }
);
