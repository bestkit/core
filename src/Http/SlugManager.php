<?php

namespace Bestkit\Http;

use Illuminate\Support\Arr;

class SlugManager
{
    protected $drivers = [];

    public function __construct(array $drivers)
    {
        $this->drivers = $drivers;
    }

    /**
     * @template T of \Bestkit\Database\AbstractModel
     * @param class-string<T> $resourceName
     * @return SlugDriverInterface<T>
     */
    public function forResource(string $resourceName): SlugDriverInterface
    {
        return Arr::get($this->drivers, $resourceName, null);
    }
}
