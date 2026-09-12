<?php

namespace App\Models;

use App\Models\Concerns\BelongsToHotel;
use App\Models\Concerns\HasMedia;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Offer extends Model
{
    use BelongsToHotel, HasMedia;

    protected $fillable = [
        'hotel_id', 'title', 'slug', 'description', 'price',
        'discount', 'valid_from', 'valid_until', 'booking_url', 'status', 'featured',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'valid_from' => 'date',
        'valid_until' => 'date',
        'featured' => 'boolean',
    ];

    protected $appends = ['cover_image_url'];

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published');
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('featured', true);
    }

    /**
     * Published AND not past its valid_until date. Deliberately a scope,
     * not a stored status - an offer's expiry is a function of "today"
     * vs. its dates, not something an admin should have to remember to
     * flip manually. The Page Builder's `offers` section defaults to
     * active_only=true, resolving against this.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->published()
            ->where(fn ($q) => $q->whereNull('valid_until')->orWhere('valid_until', '>=', now()->toDateString()));
    }
}
