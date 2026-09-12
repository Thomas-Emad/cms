<?php

namespace App\Models\Concerns;

use App\Models\Media;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

/**
 * Applied to Facility, Restaurant, Event, Offer, Experience, MenuItem.
 *
 * Two collections by convention:
 *   - 'cover'   : single image, used by cards and hero-style displays
 *   - 'gallery' : ordered, many, used by detail pages and the Page
 *                 Builder's future GallerySection
 *
 * Deliberately NOT a full media-library package feature set (no
 * conversions/thumbnails/responsive images) - Phase 1's spec calls for
 * "cover_image, gallery" per entity, not an image-processing pipeline.
 * Add that later behind this same trait if/when it's actually needed,
 * without touching any model that uses it.
 */
trait HasMedia
{
    public function media(): MorphMany
    {
        return $this->morphMany(Media::class, 'mediable')->orderBy('sort_order');
    }

    public function cover(): MorphOne
    {
        return $this->morphOne(Media::class, 'mediable')
            ->where('collection', 'cover')
            ->oldest('sort_order');
    }

    public function gallery(): MorphMany
    {
        return $this->morphMany(Media::class, 'mediable')
            ->where('collection', 'gallery')
            ->orderBy('sort_order');
    }

    /**
     * Convenience accessor so simple card components can keep using
     * `entity.cover_image_url` without knowing about the media table.
     */
    public function getCoverImageUrlAttribute(): ?string
    {
        return $this->cover?->url;
    }
}
