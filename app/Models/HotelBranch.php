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
    ];

    protected $casts = [
        'gallery_urls' => 'array',
        'features' => 'array',
        'is_main' => 'boolean',
        'latitude' => 'float',
        'longitude' => 'float',
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
     * Return all photos for this branch (cover photo + gallery photos).
     *
     * @return string[]
     */
    public function getAllPhotosAttribute(): array
    {
        $photos = [];
        if ($this->cover_image_url) {
            $photos[] = $this->cover_image_url;
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
