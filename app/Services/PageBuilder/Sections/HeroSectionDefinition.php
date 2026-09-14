<?php

namespace App\Services\PageBuilder\Sections;

use App\Models\Hotel;
use App\Services\PageBuilder\Concerns\ResolvesMedia;
use App\Services\PageBuilder\Contracts\SectionDefinition;

class HeroSectionDefinition implements SectionDefinition
{
    use ResolvesMedia;

    public function type(): string
    {
        return 'hero';
    }

    public function propsSchema(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'subtitle' => ['nullable', 'string', 'max:500'],
            // Reference, not embedded content - re-validated against the
            // page's own hotel_id at resolve time (see PageRenderService).
            'media_id' => ['nullable', 'integer', 'exists:media,id'],
            'button_text' => ['nullable', 'string', 'max:100'],
            'button_url' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function defaultProps(): array
    {
        return [
            'title' => '',
            'subtitle' => '',
            'media_id' => null,
            'button_text' => '',
            'button_url' => '',
        ];
    }

    public function isDynamic(): bool
    {
        return false;
    }

    public function resolve(array $props, Hotel $hotel, mixed $entity = null): array
    {
        return [
            'media' => $this->resolveMedia($props['media_id'] ?? null, $hotel),
        ];
    }
}
