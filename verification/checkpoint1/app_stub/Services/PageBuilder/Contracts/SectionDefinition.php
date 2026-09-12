<?php

namespace App\Services\PageBuilder\Contracts;

use App\Models\Hotel;

interface SectionDefinition
{
    /**
     * The registry key used in JSON, e.g. 'facility-grid'.
     */
    public function type(): string;

    /**
     * Laravel validation rules for this section's `props`. Validated on
     * every draft save - this is the actual security boundary (the
     * frontend registry's field types are UX convenience only).
     */
    public function propsSchema(): array;

    public function defaultProps(): array;

    /**
     * True for sections that query the database (facility-grid,
     * restaurant-grid, events, ...). False for pure-content sections
     * (hero, text, cta, spacer) whose resolve() is a no-op.
     *
     * Drives two things: (1) the frontend only debounces/network-calls
     * the live preview resolver for dynamic sections - static ones
     * re-render instantly and purely client-side; (2) PageRenderService
     * can skip calling resolve() at all for non-dynamic sections as a
     * minor optimization, though calling it is always safe since it's a
     * documented no-op.
     */
    public function isDynamic(): bool;

    /**
     * Turn validated props into render-ready data. For dynamic sections
     * this runs a tenant-scoped query (via the hotel's own
     * BelongsToHotel-scoped models). For static sections this returns [].
     *
     * @return array Render-ready data, shaped however this section type's
     *                Vue component expects it (e.g. a list of facilities).
     */
    public function resolve(array $props, Hotel $hotel): array;
}
