<?php

namespace App\Services\PageBuilder\Sections;

use App\Models\Hotel;
use App\Services\PageBuilder\Concerns\ResolvesMedia;
use App\Services\PageBuilder\Contracts\SectionDefinition;

class GallerySectionDefinition implements SectionDefinition
{
    use ResolvesMedia;

    public function type(): string
    {
        return 'gallery';
    }

    public function propsSchema(): array
    {
        return [
            'title' => ['nullable', 'string', 'max:255'],
            'media_ids' => ['required', 'array', 'min:1', 'max:20'],
            'media_ids.*' => ['integer', 'exists:media,id'],
        ];
    }

    public function defaultProps(): array
    {
        return ['title' => '', 'media_ids' => []];
    }

    public function isDynamic(): bool
    {
        return false; // see ImageSectionDefinition's docblock - same reasoning
    }

    public function resolve(array $props, Hotel $hotel, mixed $entity = null): array
    {
        return ['media' => $this->resolveMediaList($props['media_ids'] ?? [], $hotel)];
    }
}
