<?php

namespace App\Services\Layout;

/**
 * The guest-screen layout template and its options (which template, menu items, tile size ...).
 *
 * Pure PHP (no framework dependencies) so it can be tested on its own. The defaults live in ONE
 * file, resources/js/Layouts/guest/layout-defaults.json, that the frontend imports too, so the two
 * sides cannot drift apart.
 */
final class GuestLayoutConfig
{
    public const TEMPLATES = ['classic', 'tv'];

    public const TILE_SIZES = ['small', 'medium', 'large'];

    public const MAX_ITEMS = 12;

    public static function defaults(): array
    {
        static $cache = null;

        return $cache ??= json_decode((string) file_get_contents(__DIR__.'/../../../resources/js/Layouts/guest/layout-defaults.json'), true);
    }

    /** Length in characters (not bytes); works with or without the mbstring extension. */
    private static function len(string $s): int
    {
        return function_exists('mb_strlen') ? mb_strlen($s) : (int) preg_match_all('/./us', $s);
    }

    /** @return string[] human-readable problems; empty = valid */
    public static function validate(mixed $in): array
    {
        $e = [];
        if (! is_array($in)) {
            return ['The layout settings must be an object.'];
        }
        if (! in_array($in['template'] ?? null, self::TEMPLATES, true)) {
            $e[] = 'Choose a layout template.';
        }
        foreach (['show_clock', 'lock_home_scroll'] as $k) {
            if (! is_bool($in[$k] ?? null)) {
                $e[] = "\"$k\" must be on or off.";
            }
        }
        if (! in_array($in['tile_size'] ?? null, self::TILE_SIZES, true)) {
            $e[] = 'Tile size must be small, medium or large.';
        }
        foreach (['tagline', 'headline'] as $k) {
            $v = $in[$k] ?? '';
            if (! is_string($v) && $v !== null) {
                $e[] = "\"$k\" must be text.";
            } elseif (is_string($v) && self::len($v) > 80) {
                $e[] = "\"$k\" can be at most 80 characters.";
            }
        }

        $items = $in['items'] ?? null;
        if (! is_array($items) || ! array_is_list($items) || count($items) < 1 || count($items) > self::MAX_ITEMS) {
            $e[] = 'Add between 1 and '.self::MAX_ITEMS.' menu items.';

            return $e;
        }
        $seen = [];
        $visible = 0;
        foreach ($items as $i => $it) {
            $n = $i + 1;
            if (! is_array($it)) {
                $e[] = "Menu item $n is invalid.";

                continue;
            }
            $href = $it['href'] ?? null;
            if (! is_string($href) || strlen($href) > 120 || ! str_starts_with($href, '/') || str_starts_with($href, '//') || preg_match('/[\s<>"\']/', $href)) {
                $e[] = "Menu item $n: the page address must start with / (for example /facilities).";
            } elseif (isset($seen[$href])) {
                $e[] = "Menu item $n: \"$href\" is used twice.";
            } else {
                $seen[$href] = true;
            }
            $label = $it['label'] ?? null;
            if (! is_string($label) || trim($label) === '' || self::len($label) > 30) {
                $e[] = "Menu item $n needs a name (up to 30 characters).";
            }
            $icon = $it['icon'] ?? '';
            if (! is_string($icon) && $icon !== null) {
                $e[] = "Menu item $n: the icon must be text.";
            } elseif (is_string($icon) && self::len($icon) > 8) {
                $e[] = "Menu item $n: the icon must be a single emoji or symbol.";
            }
            $color = $it['color'] ?? null;
            if ($color !== null && $color !== '' && ! (is_string($color) && preg_match('/^#[0-9a-fA-F]{6}$/', $color))) {
                $e[] = "Menu item $n: the tile colour must look like #1f5c4a.";
            }
            if (! is_bool($it['visible'] ?? null)) {
                $e[] = "Menu item $n: shown/hidden must be on or off.";
            } elseif ($it['visible']) {
                $visible++;
            }
        }
        if ($visible === 0) {
            $e[] = 'Show at least one menu item.';
        }

        return $e;
    }

    /**
     * Turn whatever is stored (or nothing) into a complete, valid config. Never throws: an old or
     * hand-edited value that no longer validates falls back to the defaults for that part.
     */
    public static function normalize(mixed $raw): array
    {
        $d = self::defaults();
        $raw = is_array($raw) ? $raw : [];
        $out = $d;

        if (in_array($raw['template'] ?? null, self::TEMPLATES, true)) {
            $out['template'] = $raw['template'];
        }
        foreach (['show_clock', 'lock_home_scroll'] as $k) {
            if (is_bool($raw[$k] ?? null)) {
                $out[$k] = $raw[$k];
            }
        }
        if (in_array($raw['tile_size'] ?? null, self::TILE_SIZES, true)) {
            $out['tile_size'] = $raw['tile_size'];
        }
        foreach (['tagline', 'headline'] as $k) {
            if (is_string($raw[$k] ?? null) && self::len($raw[$k]) <= 80) {
                $out[$k] = trim($raw[$k]);
            }
        }
        if (isset($raw['items']) && self::validate(array_merge($d, $raw, ['template' => 'classic'])) === []) {
            $out['items'] = array_map(fn ($it) => [
                'href' => $it['href'],
                'label' => trim($it['label']),
                'icon' => (string) ($it['icon'] ?? ''),
                'color' => ($it['color'] ?? '') === '' ? null : strtolower($it['color']),
                'visible' => $it['visible'],
            ], $raw['items']);
        }

        return $out;
    }
}
