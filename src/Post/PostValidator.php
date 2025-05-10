<?php

namespace Bestkit\Post;

use Bestkit\Foundation\AbstractValidator;

class PostValidator extends AbstractValidator
{
    protected $rules = [
        'content' => [
            'required',
            'max:65535'
        ]
    ];
}
