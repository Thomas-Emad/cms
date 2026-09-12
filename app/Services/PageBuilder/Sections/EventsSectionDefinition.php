<?php

namespace App\Services\PageBuilder\Sections;

use App\Models\Event;
use App\Models\Hotel;
use App\Services\PageBuilder\Contracts\SectionDefinition;

class EventsSectionDefinition implements SectionDefinition
{
    public function type(): string
    {
        return 'events';
    }

    public function propsSchema(): array
    {
        return [
            'title' => ['nullable', 'string', 'max:255'],
            'limit' => ['integer', 'min:1', 'max:12'],
            'upcoming_only' => ['boolean'],
        ];
    }

    public function defaultProps(): array
    {
        return ['title' => 'Upcoming Events', 'limit' => 4, 'upcoming_only' => true];
    }

    public function isDynamic(): bool
    {
        return true;
    }

    /**
     * `upcoming_only` (default true) resolves against Event::scopeUpcoming()
     * exactly as documented when that scope was built in Phase 2 - "the
     * Page Builder's `events` section defaults to upcoming_only=true,
     * resolving against this." When false, falls back to published() with
     * a simple date ordering rather than inventing a second "all events"
     * scope on the model.
     */
    public function resolve(array $props, Hotel $hotel): array
    {
        $query = Event::query()->published();

        $query = ($props['upcoming_only'] ?? true)
            ? $query->upcoming()
            : $query->orderBy('start_date');

        $events = $query->with('cover')->limit($props['limit'] ?? 4)->get();

        return [
            'events' => $events->map(fn (Event $e) => [
                ...$e->only(['id', 'title', 'slug', 'start_date', 'start_time', 'location']),
                'cover_image_url' => $e->cover_image_url,
            ])->all(),
        ];
    }
}
