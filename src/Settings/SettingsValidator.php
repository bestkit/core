<?php

namespace Bestkit\Settings;

use Bestkit\Foundation\AbstractValidator;

class SettingsValidator extends AbstractValidator
{
    /**
     * These rules apply to all attributes.
     *
     * Entries in the default DB settings table are limited to 65,000
     * characters. We validate against this to avoid confusing errors.
     *
     * @var array
     */
    protected $globalRules = [
        'max:65000',
    ];

    /**
     * Make a new validator instance for this model.
     *
     * @param array $attributes
     * @return \Illuminate\Validation\Validator
     */
    protected function makeValidator(array $attributes)
    {
        // Apply global rules first.
        $rules = array_map(function () {
            return $this->globalRules;
        }, $attributes);

        // Apply attribute specific rules.
        foreach ($rules as $key => $value) {
            $rules[$key] = array_merge($rules[$key], $this->rules[$key] ?? []);
        }

        $validator = $this->validator->make($attributes, $rules, $this->getMessages());

        foreach ($this->configuration as $callable) {
            $callable($this, $validator);
        }

        return $validator;
    }
}
