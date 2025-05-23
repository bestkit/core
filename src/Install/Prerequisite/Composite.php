<?php

namespace Bestkit\Install\Prerequisite;

use Illuminate\Support\Collection;

class Composite implements PrerequisiteInterface
{
    /**
     * @var PrerequisiteInterface[]
     */
    protected $prerequisites = [];

    public function __construct(PrerequisiteInterface $first)
    {
        foreach (func_get_args() as $prerequisite) {
            $this->prerequisites[] = $prerequisite;
        }
    }

    public function problems(): Collection
    {
        return array_reduce(
            $this->prerequisites,
            function (Collection $errors, PrerequisiteInterface $condition) {
                return $errors->concat($condition->problems());
            },
            new Collection
        );
    }
}
