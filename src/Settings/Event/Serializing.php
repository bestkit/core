<?php

namespace Bestkit\Settings\Event;

class Serializing
{
    /**
     * The settings key being saved.
     *
     * @var string
     */
    public $key;

    /**
     * The settings value to save.
     *
     * @var string
     */
    public $value;

    /**
     * @param string $key The settings key being saved.
     * @param string $value The settings value to save.
     */
    public function __construct($key, &$value)
    {
        $this->key = $key;
        $this->value = &$value;
    }
}
