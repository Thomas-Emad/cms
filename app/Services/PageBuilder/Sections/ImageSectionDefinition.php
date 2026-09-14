<?php

namespace App\Services\PageBuilder\Sections;

use App\Models\Hotel;
use App\Services\PageBuilder\Concerns\ResolvesMedia;
use App\Services\PageBuilder\Contracts\SectionDefinition;

class ImageSectionDefinition implements SectionDefinition
{
    use ResolvesMedia;

    public function type(): string
    {
        return 'image';
    }

    public function propsSchema(): array
    {
        return [
            'media_id' => ['required', 'integer', 'exists:media,id'],
            'caption' => ['nullable', 'string', 'max:255'],
            'link_url' => ['nullable', 'url', 'max:255'],
        ];
    }

    public function defaultProps(): array
    {
        return ['media_id' => null, 'caption' => '', 'link_url' => ''];
    }

    /**
     * Not "dynamic" in the frontend-live-preview sense (see interface
     * docblock) - an image's media reference is edited as a raw number
     * for now (no picker yet), which already goes through the
     * IMMEDIATE (non-batched) props path, so there's no debounced-typing
     * concern that live-preview-resolve was built to solve. Full preview
     * and guest render still correctly resolve the real URL via
     * resolve() below regardless of this flag.
     */
    public function isDynamic(): bool
    {
        return false;
    }

    public function resolve(array $props, Hotel $hotel, mixed $entity = null): array
    {
        $media = $this->resolveMedia($props['media_id'] ?? null, $hotel);

        return ['media' => $media];
    }
}
