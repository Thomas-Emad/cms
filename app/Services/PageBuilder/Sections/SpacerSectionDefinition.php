<?php

namespace App\Services\PageBuilder\Sections;

use App\Models\Hotel;
use App\Services\PageBuilder\Contracts\SectionDefinition;
use Illuminate\Validation\Rule;

/**
 * Originally specced (Phase 3 design doc §5) as having NO props at all,
 * with `height` as a `settings` value instead. Implemented here with
 * `height` as a PROP: the generic SettingsPanel only edits `props` via
 * `editorFields` in this checkpoint - there is no settings-editing UI yet
 * (that's a styling-controls concern, explicitly out of scope this
 * checkpoint). Putting `height` in `settings` would make it uneditable
 * through the Builder with no workaround; putting it in `props` costs
 * nothing architecturally (props/settings are just two JSON objects) and
 * makes it immediately usable. Revisit if/when settings-editing UI is
 * built and a real distinction between the two matters more.
 */
class SpacerSectionDefinition implements SectionDefinition
{
    public function type(): string
    {
        return 'spacer';
    }

    public function propsSchema(): array
    {
        return [
            'height' => ['required', Rule::in(['small', 'medium', 'large'])],
        ];
    }

    public function defaultProps(): array
    {
        return ['height' => 'medium'];
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
