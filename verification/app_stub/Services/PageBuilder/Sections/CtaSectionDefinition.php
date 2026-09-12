<?php

namespace App\Services\PageBuilder\Sections;

use App\Models\Hotel;
use App\Services\PageBuilder\Contracts\SectionDefinition;

class CtaSectionDefinition implements SectionDefinition
{
    public function type(): string
    {
        return 'cta';
    }

    public function propsSchema(): array
    {
        return [
            'heading' => ['required', 'string', 'max:255'],
            'subheading' => ['nullable', 'string', 'max:500'],
            'button_text' => ['required', 'string', 'max:100'],
            'button_url' => ['required', 'string', 'max:255'],
        ];
    }

    public function defaultProps(): array
    {
        return ['heading' => '', 'subheading' => '', 'button_text' => '', 'button_url' => ''];
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
