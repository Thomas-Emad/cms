<?php

namespace App\Services\PageBuilder\Sections;

use App\Models\Hotel;
use App\Services\PageBuilder\Concerns\ResolvesMedia;
use App\Services\PageBuilder\Contracts\SectionDefinition;

/**
 * Full-screen "story" slideshow for the guest screen's first view: photos
 * auto-advance with a progress bar per slide, tap right/left to skip.
 *
 * Structurally identical to the gallery section (a list of media ids
 * resolved tenant-safely), so it reuses ResolvesMedia rather than adding a
 * new media path. Not dynamic for the same reason gallery isn't: it only
 * dereferences media ids, it never queries hotel content.
 */
class StorySlideshowSectionDefinition implements SectionDefinition
{
    use ResolvesMedia;

    public function type(): string
    {
        return 'story-slideshow';
    }

    public function propsSchema(): array
    {
        return [
            'title' => ['nullable', 'string', 'max:255'],
            'subtitle' => ['nullable', 'string', 'max:255'],
            'media_ids' => ['required', 'array', 'min:1', 'max:12'],
            'media_ids.*' => ['integer', 'exists:media,id'],
            'interval_seconds' => ['nullable', 'integer', 'min:3', 'max:20'],
        ];
    }

    public function defaultProps(): array
    {
        return ['title' => '', 'subtitle' => '', 'media_ids' => [], 'interval_seconds' => 6];
    }

    public function isDynamic(): bool
    {
        return false;
    }

    public function resolve(array $props, Hotel $hotel, mixed $entity = null): array
    {
        return ['media' => $this->resolveMediaList($props['media_ids'] ?? [], $hotel)];
    }
}
