<?php

namespace Bestkit\Api;

use Bestkit\Foundation\AbstractValidator;

class ForgotPasswordValidator extends AbstractValidator
{
    /**
     * {@inheritdoc}
     */
    protected $rules = [
        'email' => ['required', 'email']
    ];
}
