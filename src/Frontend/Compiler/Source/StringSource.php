<?php

namespace Bestkit\Frontend\Compiler\Source;

/**
 * @internal
 */
class StringSource implements SourceInterface
{
    /**
     * @var callable
     */
    protected $callback;

    private $content;

    /**
     * @param callable $callback
     */
    public function __construct(callable $callback)
    {
        $this->callback = $callback;
    }

    /**
     * @return string
     */
    public function getContent(): string
    {
        if (is_null($this->content)) {
            $this->content = call_user_func($this->callback);
        }

        return $this->content;
    }

    /**
     * @return mixed
     */
    public function getCacheDifferentiator()
    {
        return $this->getContent();
    }
}
