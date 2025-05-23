<?php

namespace Bestkit\Database\Exception;

use Exception;

class MigrationKeyMissing extends Exception
{
    protected $direction;

    public function __construct(string $direction, string $file = null)
    {
        $this->direction = $direction;

        $fileNameWithSpace = $file ? ' '.realpath($file) : '';
        parent::__construct("Migration file$fileNameWithSpace should contain an array with up/down (looking for $direction)");
    }

    public function withFile(string $file = null): self
    {
        return new self($this->direction, $file);
    }
}
