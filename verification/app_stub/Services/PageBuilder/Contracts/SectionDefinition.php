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
     * Controls ONE thing: whether the frontend's live in-canvas preview
     * (checkpoint 1 §2 / checkpoint 2's resolve-preview endpoint) fires a
     * debounced network call when this section's props change while
     * editing. It is NOT a statement that resolve() never touches the
     * database - `image` and `gallery` are marked false here despite
     * their resolve() doing a Media lookup, because their media
     * reference is currently edited as a plain number (no live-typing
     * concern the debounce mechanism was built to solve, and no media
     * picker yet to make rapid changes likely). True for facility-grid,
     * restaurant-grid, service-grid, events, offers, experiences -
     * content-collection sections where the admin actively tunes filters
     * (category/featured_only/limit) and benefits from seeing the canvas
     * update live as they do.
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
