<?php

namespace Bestkit\Frontend\Compiler;

interface VersionerInterface
{
    public function putRevision(string $file, ?string $revision);

    public function getRevision(string $file): ?string;
}
