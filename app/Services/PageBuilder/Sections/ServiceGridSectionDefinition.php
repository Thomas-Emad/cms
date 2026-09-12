<?php

namespace App\Services\PageBuilder\Sections;

use App\Models\Hotel;
use App\Models\Service;
use App\Services\PageBuilder\Contracts\SectionDefinition;

class ServiceGridSectionDefinition implements SectionDefinition
{
    public function type(): string
    {
        return 'service-grid';
    }

    public function propsSchema(): array
    {
        return [
            'title' => ['nullable', 'string', 'max:255'],
            'limit' => ['integer', 'min:1', 'max:12'],
        ];
    }

    public function defaultProps(): array
    {
        return ['title' => 'Hotel Services', 'limit' => 6];
    }

    public function isDynamic(): bool
    {
        return true;
    }

    /**
     * No category/featured_only props - Services don't have those
     * concepts in Phase 2's schema (no `category` or `featured` column on
     * the services table), so this section doesn't invent filters the
     * underlying model can't actually support.
     */
    public function resolve(array $props, Hotel $hotel): array
    {
        $services = Service::query()
            ->published()
            ->ordered()
            ->limit($props['limit'] ?? 6)
            ->get();

        return [
            'services' => $services->map(fn (Service $s) => $s->only([
                'id', 'name', 'slug', 'description', 'icon', 'price', 'request_enabled', 'contact',
            ]))->all(),
        ];
    }
}
