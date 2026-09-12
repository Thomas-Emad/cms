<?php

namespace App\Services\PageBuilder\Sections;

use App\Models\Experience;
use App\Models\Hotel;
use App\Services\PageBuilder\Contracts\SectionDefinition;

class ExperiencesSectionDefinition implements SectionDefinition
{
    public function propsSchema(): array
    {
        return [
            'title' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:500'],
            'category' => ['nullable', 'string', 'max:100'],
            'featured_only' => ['boolean'],
            'limit' => ['integer', 'min:1', 'max:12'],
        ];
    }

    public function type(): string
    {
        return 'experiences';
    }

    public function defaultProps(): array
    {
        return ['title' => 'Experiences', 'description' => null, 'category' => null, 'featured_only' => false, 'limit' => 4];
    }

    public function isDynamic(): bool
    {
        return true;
    }

    public function resolve(array $props, Hotel $hotel): array
    {
        $experiences = Experience::query()
            ->published()
            ->category($props['category'] ?? null)
            ->when($props['featured_only'] ?? false, fn ($q) => $q->featured())
            ->with('cover')
            ->ordered()
            ->limit($props['limit'] ?? 4)
            ->get();

        return [
            'experiences' => $experiences->map(fn (Experience $e) => [
                ...$e->only(['id', 'title', 'slug', 'category', 'duration', 'price']),
                'cover_image_url' => $e->cover_image_url,
            ])->all(),
        ];
    }
}
