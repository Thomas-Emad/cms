<?php

namespace App\Services\PageBuilder\Sections;

use App\Models\Hotel;
use App\Services\PageBuilder\Concerns\ResolvesMedia;
use App\Services\PageBuilder\Contracts\SectionDefinition;

/**
 * The Samsung-Smart-TV-style home screen: a full-bleed hero image/tagline
 * with a row of app tiles underneath (Netflix, YouTube, Prime Video...).
 * Meant to be the single section on a 'fullscreen' layout page (see
 * migration 2026_09_21_000001 / PageView.vue) - it fills the viewport
 * itself and has no internal scrolling by design.
 *
 * App tiles are one plain-text field, one row per line: "Label | URL"
 * (the URL is optional - a line with no "|" is just a label, non-clickable).
 * Same deliberate choice as InfoListSectionDefinition::$items_text: this
 * avoids needing a repeater field type in the Builder (today's
 * SettingsPanel only has scalar field types) - a textarea is editable
 * right now and is parsed on the frontend. Rendered via text
 * interpolation only, never v-html.
 */
class AppLauncherSectionDefinition implements SectionDefinition
{
    use ResolvesMedia;

    public function type(): string
    {
        return 'app-launcher';
    }

    public function propsSchema(): array
    {
        return [
            'eyebrow' => ['nullable', 'string', 'max:100'],
            'title' => ['nullable', 'string', 'max:255'],
            'subtitle' => ['nullable', 'string', 'max:255'],
            'media_id' => ['nullable', 'integer', 'exists:media,id'],
            'apps_text' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function defaultProps(): array
    {
        return [
            'eyebrow' => '',
            'title' => '',
            'subtitle' => '',
            'media_id' => null,
            'apps_text' => "Apps\nYouTube | https://youtube.com\nNetflix | https://netflix.com\nPrime Video | https://primevideo.com\nDisney+ | https://disneyplus.com",
        ];
    }

    public function isDynamic(): bool
    {
        return false;
    }

    public function resolve(array $props, Hotel $hotel, mixed $entity = null): array
    {
        return [
            'media' => $this->resolveMedia($props['media_id'] ?? null, $hotel),
        ];
    }
}
