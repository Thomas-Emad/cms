<?php

namespace App\Models;

use App\Models\Concerns\BelongsToHotel;
use App\Models\Concerns\HasMedia;
use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    use BelongsToHotel, HasMedia, HasTranslations;

    protected array $translatable = [
        'name', 'short_description', 'description', 'bed_type', 'view',
    ];

    protected $fillable = [
        'hotel_id', 'hotel_branch_id', 'name', 'slug', 'short_description', 'description',
        'size_sqm', 'max_guests', 'bed_type', 'view', 'features',
        'status', 'featured', 'sort_order',
    ];

    protected $casts = [
        'features' => 'array',
        'size_sqm' => 'integer',
        'max_guests' => 'integer',
        'featured' => 'boolean',
    ];

    protected $appends = ['cover_image_url'];

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published');
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }
}
