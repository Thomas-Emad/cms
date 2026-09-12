<?php

namespace App\Models;

use App\Models\Concerns\BelongsToHotel;
use App\Models\Concerns\HasMedia;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Restaurant extends Model
{
    use BelongsToHotel, HasMedia;

    protected $fillable = [
        'hotel_id', 'name', 'slug', 'description', 'cuisine', 'location', 'floor',
        'opening_hours', 'dress_code', 'phone', 'reservation_url',
        'status', 'featured', 'sort_order',
    ];

    protected $casts = [
        'opening_hours' => 'array',
        'featured' => 'boolean',
    ];

    protected $appends = ['cover_image_url'];

    public function menus(): HasMany
    {
        return $this->hasMany(Menu::class);
    }

    /**
     * The menu guests actually see. A restaurant could technically have
     * multiple Menu rows (e.g. seasonal), but only one is_active at a time
     * -- same "draft vs published" spirit as Page Builder pages, kept
     * intentionally simpler since a full versioned-menu workflow isn't a
     * Phase 2 requirement.
     */
    public function activeMenu(): HasOne
    {
        return $this->hasOne(Menu::class)->where('is_active', true);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published');
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
