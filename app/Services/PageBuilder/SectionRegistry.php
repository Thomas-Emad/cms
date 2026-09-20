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
use App\Services\PageBuilder\Sections\InfoListSectionDefinition;
use App\Services\PageBuilder\Sections\OffersSectionDefinition;
use App\Services\PageBuilder\Sections\RestaurantGallerySectionDefinition;
use App\Services\PageBuilder\Sections\RestaurantGridSectionDefinition;
use App\Services\PageBuilder\Sections\RestaurantHeroSectionDefinition;
use App\Services\PageBuilder\Sections\RestaurantInfoSectionDefinition;
use App\Services\PageBuilder\Sections\RestaurantLocationSectionDefinition;
use App\Services\PageBuilder\Sections\RestaurantMenuSectionDefinition;
use App\Services\PageBuilder\Sections\ServiceGridSectionDefinition;
use App\Services\PageBuilder\Sections\SpacerSectionDefinition;
use App\Services\PageBuilder\Sections\StorySlideshowSectionDefinition;
use App\Services\PageBuilder\Sections\TextSectionDefinition;
use InvalidArgumentException;

/**
 * One engine for both standalone Pages and entity-backed presentations.
 * `$contexts` is a lightweight tag per type - NOT a second registry, just
 * metadata used to filter the frontend Section Library so a Restaurant's
 * Builder doesn't show "Facility Grid" and a standalone Page's Builder
 * doesn't show "Restaurant Menu". Every type still goes through the exact
 * same resolve()/validate()/render pipeline regardless of context.
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
        'story-slideshow' => StorySlideshowSectionDefinition::class,
        'info-list' => InfoListSectionDefinition::class,
        'restaurant-hero' => RestaurantHeroSectionDefinition::class,
        'restaurant-info' => RestaurantInfoSectionDefinition::class,
        'restaurant-gallery' => RestaurantGallerySectionDefinition::class,
        'restaurant-menu' => RestaurantMenuSectionDefinition::class,
        'restaurant-location' => RestaurantLocationSectionDefinition::class,
    ];

    /**
     * @var array<string, string[]> type => contexts it's relevant in.
     *      'any' means it shows up in every Builder regardless of context.
     */
    private static array $contexts = [
        'hero' => ['page'],
        'text' => ['any'],
        'image' => ['any'],
        'gallery' => ['page'],
        'facility-grid' => ['page'],
        'restaurant-grid' => ['page'],
        'service-grid' => ['page'],
        'events' => ['page'],
        'offers' => ['page'],
        'experiences' => ['page'],
        'cta' => ['any'],
        'spacer' => ['any'],
        'story-slideshow' => ['page'],
        'info-list' => ['any'],
        'restaurant-hero' => ['restaurant'],
        'restaurant-info' => ['restaurant'],
        'restaurant-gallery' => ['restaurant'],
        'restaurant-menu' => ['restaurant'],
        'restaurant-location' => ['restaurant'],
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

    /** @return string[] */
    public static function contextsFor(string $type): array
    {
        return self::$contexts[$type] ?? ['any'];
    }

    /** @return string[] Types relevant to a given Builder context ('page', 'restaurant', ...). */
    public static function typesForContext(string $context): array
    {
        return array_values(array_filter(
            self::types(),
            fn ($type) => in_array($context, self::contextsFor($type), true) || in_array('any', self::contextsFor($type), true)
        ));
    }
}
