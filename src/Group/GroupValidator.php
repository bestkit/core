<?php

namespace Bestkit\Group;

use Bestkit\Foundation\AbstractValidator;

class GroupValidator extends AbstractValidator
{
    protected $rules = [
        'name_singular' => ['required'],
        'name_plural' => ['required']
    ];
}
