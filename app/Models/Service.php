<?php

namespace App\Models;

use App\Models\Concerns\BelongsToHotel;
use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * No HasMedia/gallery: services are represented by an icon (see `icon`
 * column - a key into a small predefined icon set, not an uploaded image)
 * per Section 10 of the spec ("icon/image"). If a hotel later wants photo
 * imagery per service, add HasMedia the same way Facility/Restaurant did -
 * no migration rework needed since `media` is already polymorphic.
 */
class Service extends Model
{
    use BelongsToHotel, HasTranslations;

    protected array $translatable = [
        'name', 'description', 'availability',
    ];

    protected $fillable = [
        'hotel_id', 'name', 'slug', 'description', 'icon', 'availability',
        'contact', 'price', 'request_enabled', 'status', 'sort_order',
    ];

    protected $casts = [
        'availability' => 'array',
        'price' => 'decimal:2',
        'request_enabled' => 'boolean',
    ];

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published');
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }
}
