<?php

namespace App\Models;

use App\Models\Concerns\BelongsToHotel;
use App\Models\Concerns\HasMedia;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Facility extends Model
{
    use BelongsToHotel, HasMedia;

    protected $fillable = [
        'hotel_id', 'name', 'slug', 'description', 'short_description', 'category',
        'building', 'floor', 'wing', 'pos_x', 'pos_y',
        'opening_hours', 'phone', 'email', 'amenities', 'status', 'featured', 'sort_order',
    ];

    protected $casts = [
        'opening_hours' => 'array',
        'amenities' => 'array',
        'pos_x' => 'float',
        'pos_y' => 'float',
        'featured' => 'boolean',
    ];

    protected $appends = ['cover_image_url'];

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published');
    }

    public function scopeCategory(Builder $query, ?string $category): Builder
    {
        return $category ? $query->where('category', $category) : $query;
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('featured', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }
}
