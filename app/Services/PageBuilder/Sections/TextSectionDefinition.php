<?php

namespace App\Services\PageBuilder\Sections;

use App\Models\Hotel;
use App\Services\PageBuilder\Contracts\SectionDefinition;

/**
 * `body` is plain text with line breaks only in this checkpoint - no
 * markdown/rich-text parsing introduced yet. The Vue component renders it
 * via text interpolation with `white-space: pre-line`, never `v-html`.
 * A constrained-markdown upgrade (bold/italic/links/lists only, no raw
 * HTML passthrough) is a later, separate decision - not needed to prove
 * the pipeline in this checkpoint, and deferring it avoids picking a
 * markdown-sanitization approach before it's actually needed.
 */
class TextSectionDefinition implements SectionDefinition
{
    public function type(): string
    {
        return 'text';
    }

    public function propsSchema(): array
    {
        return [
            'heading' => ['nullable', 'string', 'max:255'],
            'body' => ['nullable', 'string', 'max:5000'],
        ];
    }

    public function defaultProps(): array
    {
        return ['heading' => '', 'body' => ''];
    }

    public function isDynamic(): bool
    {
        return false;
    }

    public function resolve(array $props, Hotel $hotel): array
    {
        return [];
    }
}
