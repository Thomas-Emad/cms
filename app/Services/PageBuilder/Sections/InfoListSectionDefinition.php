<?php

namespace App\Services\PageBuilder\Sections;

use App\Models\Hotel;
use App\Services\PageBuilder\Contracts\SectionDefinition;

/**
 * A big, readable "label ........ value" list for guest screens: opening
 * times, short-dial numbers, room types, and similar.
 *
 * Content is one plain-text field, one row per line, "Label | Value".
 * A line without "|" becomes a group heading. This deliberately avoids a
 * repeater field type in the Builder (the existing editor only has scalar
 * field types) - a textarea is editable with the current SettingsPanel and
 * is parsed on the frontend. Rendered via text interpolation only, never
 * v-html, same as the text section.
 */
class InfoListSectionDefinition implements SectionDefinition
{
    public function type(): string
    {
        return 'info-list';
    }

    public function propsSchema(): array
    {
        return [
            'title' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:500'],
            'items_text' => ['nullable', 'string', 'max:4000'],
        ];
    }

    public function defaultProps(): array
    {
        return ['title' => '', 'description' => '', 'items_text' => ''];
    }

    public function isDynamic(): bool
    {
        return false;
    }

    public function resolve(array $props, Hotel $hotel, mixed $entity = null): array
    {
        return [];
    }
}
