<?php

namespace App\Models;

use App\Models\Concerns\BelongsToHotel;
use App\Models\Concerns\HasMedia;
use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HotelBranch extends Model
{
    use BelongsToHotel, HasFactory, HasMedia, HasTranslations;

    protected array $translatable = [
        'name', 'city', 'address', 'description', 'short_description',
    ];

    protected $fillable = [
        'hotel_id',
        'name',
        'slug',
        'domain',
        'city',
        'address',
        'phone',
        'email',
        'short_description',
        'description',
        'cover_image_url',
        'gallery_urls',
        'latitude',
        'longitude',
        'features',
        'status',
        'is_main',
        'sort_order',
        'guest_layout',
        'metadata',
    ];

    protected $casts = [
        'gallery_urls' => 'array',
        'features' => 'array',
        'is_main' => 'boolean',
        'latitude' => 'float',
        'longitude' => 'float',
        'metadata' => 'array',
    ];

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published');
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderByDesc('is_main')->orderBy('sort_order')->orderBy('name');
    }

    /**
     * Get cover image URL, preferring uploaded cover media before falling back to cover_image_url attribute.
     */
    public function getCoverImageUrlAttribute(): ?string
    {
        return $this->cover?->url ?: ($this->attributes['cover_image_url'] ?? null);
    }

    /**
     * Return all photos for this branch (cover photo + uploaded gallery media + gallery_urls).
     *
     * @return string[]
     */
    public function getAllPhotosAttribute(): array
    {
        $photos = [];
        $cover = $this->cover_image_url;
        if ($cover) {
            $photos[] = $cover;
        }

        $galleryMedia = $this->relationLoaded('gallery') ? $this->gallery : $this->gallery()->get();
        foreach ($galleryMedia as $media) {
            if ($media->url && ! in_array($media->url, $photos, true)) {
                $photos[] = $media->url;
            }
        }

        if (is_array($this->gallery_urls)) {
            foreach ($this->gallery_urls as $url) {
                if (is_string($url) && ! in_array($url, $photos, true)) {
                    $photos[] = $url;
                }
            }
        }

        return $photos;
    }
}
