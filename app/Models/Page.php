<?php

namespace App\Models;

use App\Models\Concerns\BelongsToHotel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Page extends Model
{
    use BelongsToHotel;

    protected $fillable = [
        'hotel_id', 'name', 'slug', 'is_home', 'layout',
        'seo_title', 'seo_description', 'seo_og_image_media_id',
        'status', 'draft_version_id', 'published_version_id',
    ];

    protected $casts = [
        'is_home' => 'boolean',
    ];

    public function versions(): HasMany
    {
        return $this->hasMany(PageVersion::class);
    }

    public function draftVersion(): BelongsTo
    {
        return $this->belongsTo(PageVersion::class, 'draft_version_id');
    }

    public function publishedVersion(): BelongsTo
    {
        return $this->belongsTo(PageVersion::class, 'published_version_id');
    }

    public function seoImage(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'seo_og_image_media_id');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published')->whereNotNull('published_version_id');
    }

    public function scopeHome(Builder $query): Builder
    {
        return $query->where('is_home', true);
    }
}
