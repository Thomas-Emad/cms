<?php

namespace App\Services\PageBuilder;

use App\Services\PageBuilder\Contracts\SectionDefinition;
use App\Services\PageBuilder\Sections\CtaSectionDefinition;
use App\Services\PageBuilder\Sections\EventsSectionDefinition;
use App\Services\PageBuilder\Sections\ExperiencesSectionDefinition;
use App\Services\PageBuilder\Sections\FacilityGridSectionDefinition;
use App\Services\PageBuilder\Sections\GallerySectionDefinition;
use App\Services\PageBuilder\Sections\HeroSectionDefinition;
use App\Services\PageBuilder\Sections\ImageSectionDefinition;
use App\Services\PageBuilder\Sections\OffersSectionDefinition;
use App\Services\PageBuilder\Sections\RestaurantGridSectionDefinition;
use App\Services\PageBuilder\Sections\ServiceGridSectionDefinition;
use App\Services\PageBuilder\Sections\SpacerSectionDefinition;
use App\Services\PageBuilder\Sections\TextSectionDefinition;
use InvalidArgumentException;

/**
 * Complete registry - all 12 section types from the Phase 3 design doc's
 * initial section list. Adding a new type beyond this is still exactly
 * one new SectionDefinition class + one line here + the matching
 * frontend registry.ts entry + updating section-types.json (see
 * SectionRegistryParityTest / registry.test.ts for why that file exists).
 */
class SectionRegistry
{
    /** @var array<string, class-string<SectionDefinition>> */
    private static array $definitions = [
        'hero' => HeroSectionDefinition::class,
        'text' => TextSectionDefinition::class,
        'image' => ImageSectionDefinition::class,
        'gallery' => GallerySectionDefinition::class,
        'facility-grid' => FacilityGridSectionDefinition::class,
        'restaurant-grid' => RestaurantGridSectionDefinition::class,
        'service-grid' => ServiceGridSectionDefinition::class,
        'events' => EventsSectionDefinition::class,
        'offers' => OffersSectionDefinition::class,
        'experiences' => ExperiencesSectionDefinition::class,
        'cta' => CtaSectionDefinition::class,
        'spacer' => SpacerSectionDefinition::class,
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
