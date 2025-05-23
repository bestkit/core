<?php

namespace Bestkit\Discussion;

use Bestkit\Database\AbstractModel;
use Bestkit\Http\SlugDriverInterface;
use Bestkit\User\User;

/**
 * @implements SlugDriverInterface<Discussion>
 */
class Utf8SlugDriver implements SlugDriverInterface
{
    /**
     * @var DiscussionRepository
     */
    protected $discussions;

    public function __construct(DiscussionRepository $discussions)
    {
        $this->discussions = $discussions;
    }

    /**
     * @param Discussion $instance
     * @return string
     */
    public function toSlug(AbstractModel $instance): string
    {
        $slug = preg_replace('/[-\s]+/u', '-', $instance->title);
        $slug = preg_replace('/[^\p{L}\p{N}\p{M}_-]+/u', '', $slug);
        $slug = strtolower($slug);

        return $instance->id.(trim($slug) ? '-'.$slug : '');
    }

    /**
     * @param string $slug
     * @param User $actor
     * @return Discussion
     */
    public function fromSlug(string $slug, User $actor): AbstractModel
    {
        if (strpos($slug, '-')) {
            $slug_array = explode('-', $slug);
            $slug = $slug_array[0];
        }

        return $this->discussions->findOrFail($slug, $actor);
    }
}
