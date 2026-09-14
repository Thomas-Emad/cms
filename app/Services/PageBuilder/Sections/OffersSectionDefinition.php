<?php

namespace App\Services\PageBuilder\Sections;

use App\Models\Hotel;
use App\Models\Offer;
use App\Services\PageBuilder\Contracts\SectionDefinition;

class OffersSectionDefinition implements SectionDefinition
{
    public function type(): string
    {
        return 'offers';
    }

    public function propsSchema(): array
    {
        return [
            'title' => ['nullable', 'string', 'max:255'],
            'limit' => ['integer', 'min:1', 'max:12'],
            'active_only' => ['boolean'],
            'featured_only' => ['boolean'],
        ];
    }

    public function defaultProps(): array
    {
        return ['title' => 'Special Offers', 'limit' => 4, 'active_only' => true, 'featured_only' => false];
    }

    public function isDynamic(): bool
    {
        return true;
    }

    /**
     * `active_only` (default true) resolves against Offer::scopeActive()
     * (published AND not past its valid_until date) exactly as documented
     * when that scope was built in Phase 2. When false, falls back to
     * plain published() so an admin can deliberately show an offer that's
     * technically expired-by-date but still marked published (e.g. an
     * "ended" banner use case) - not the common case, but not blocked.
     */
    public function resolve(array $props, Hotel $hotel, mixed $entity = null): array
    {
        $query = ($props['active_only'] ?? true)
            ? Offer::query()->active()
            : Offer::query()->published();

        $offers = $query
            ->when($props['featured_only'] ?? false, fn ($q) => $q->featured())
            ->with('cover')
            ->latest()
            ->limit($props['limit'] ?? 4)
            ->get();

        return [
            'offers' => $offers->map(fn (Offer $o) => [
                ...$o->only(['id', 'title', 'slug', 'price', 'discount', 'valid_until']),
                'cover_image_url' => $o->cover_image_url,
            ])->all(),
        ];
    }
}
