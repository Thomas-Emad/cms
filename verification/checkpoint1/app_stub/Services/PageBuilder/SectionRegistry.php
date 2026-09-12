<?php

namespace App\Services\PageBuilder;

use App\Services\PageBuilder\Contracts\SectionDefinition;
use App\Services\PageBuilder\Sections\FacilityGridSectionDefinition;
use App\Services\PageBuilder\Sections\HeroSectionDefinition;
use App\Services\PageBuilder\Sections\TextSectionDefinition;
use InvalidArgumentException;

/**
 * Adding a new section type = one new SectionDefinition class + one line
 * here. Nothing else in the application branches on section type by
 * string comparison - controllers, validation, and rendering all go
 * through this registry instead.
 */
class SectionRegistry
{
    /** @var array<string, class-string<SectionDefinition>> */
    private static array $definitions = [
        'hero' => HeroSectionDefinition::class,
        'text' => TextSectionDefinition::class,
        'facility-grid' => FacilityGridSectionDefinition::class,
        // Remaining 9 types (image, gallery, restaurant-grid, service-grid,
        // events, offers, experiences, cta, spacer) land in the next
        // checkpoint once this pattern is confirmed working end to end.
    ];

    public static function has(string $type): bool
    {
        return isset(self::$definitions[$type]);
    }

    public static function get(string $type): SectionDefinition
    {
        if (! self::has($type)) {
            throw new InvalidArgumentException("Unknown section type: {$type}");
        }

        return app(self::$definitions[$type]);
    }

    /** @return string[] */
    public static function types(): array
    {
        return array_keys(self::$definitions);
    }
}
