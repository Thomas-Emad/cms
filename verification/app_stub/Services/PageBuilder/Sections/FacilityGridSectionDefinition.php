<?php

namespace App\Services\PageBuilder\Sections;

use App\Models\Facility;
use App\Models\Hotel;
use App\Services\PageBuilder\Contracts\SectionDefinition;
use Illuminate\Validation\Rule;

class FacilityGridSectionDefinition implements SectionDefinition
{
    private const CATEGORIES = [
        'wellness', 'fitness', 'pool', 'kids', 'business', 'beach', 'meeting', 'other',
    ];

    public function type(): string
    {
        return 'facility-grid';
    }

    public function propsSchema(): array
    {
        return [
            'title' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:500'],
            'category' => ['nullable', Rule::in(self::CATEGORIES)],
            'featured_only' => ['boolean'],
            'limit' => ['integer', 'min:1', 'max:12'],
            'columns' => [Rule::in([2, 3, 4])],
        ];
    }

    public function defaultProps(): array
    {
        return [
            'title' => 'Explore Our Facilities',
            'description' => null,
            'category' => null,
            'featured_only' => false,
            'limit' => 6,
            'columns' => 3,
        ];
    }

    public function isDynamic(): bool
    {
        return true;
    }

    /**
     * The query descriptor -> actual data step. Note this never receives
     * or needs a facility ID - it's driven entirely by filter criteria, so
     * deleting/unpublishing a facility elsewhere in the system requires no
     * change to any page JSON that references "facilities in this
     * category," anywhere.
     *
     * Tenant isolation: Facility::query() is already scoped to the current
     * hotel via BelongsToHotel's global scope, which resolves off
     * CurrentHotel - the same mechanism every Phase 2 guest controller
     * uses. $hotel is accepted here for interface consistency and future
     * definitions that might need it explicitly, but the scoping itself
     * doesn't depend on this parameter - it depends on request context.
     */
    public function resolve(array $props, Hotel $hotel): array
    {
        $facilities = Facility::query()
            ->published()
            ->category($props['category'] ?? null)
            ->when($props['featured_only'] ?? false, fn ($q) => $q->featured())
            ->with('cover')
            ->ordered()
            ->limit($props['limit'] ?? 6)
            ->get();

        return [
            'facilities' => $facilities->map(fn (Facility $f) => [
                ...$f->only(['id', 'name', 'slug', 'short_description', 'category']),
                'cover_image_url' => $f->cover_image_url,
            ])->all(),
        ];
    }
}
