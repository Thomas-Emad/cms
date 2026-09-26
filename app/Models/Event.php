<?php

namespace App\Models;

use App\Models\Concerns\BelongsToHotel;
use App\Models\Concerns\HasMedia;
use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use BelongsToHotel, HasMedia, HasTranslations;

    protected array $translatable = [
        'title',
        'description',
        'short_description',
        'location',
    ];

    protected $fillable = [
        'hotel_id',
        'hotel_branch_id',
        'title',
        'slug',
        'description',
        'start_date',
        'end_date',
        'start_time',
        'end_time',
        'location',
        'capacity',
        'booking_required',
        'booking_url',
        'status',
    ];

    protected $casts = [
        'start_date' => 'date:Y-m-d',
        'end_date' => 'date:Y-m-d',
        'booking_required' => 'boolean',
        'created_at' => 'datetime:Y-m-d H:i:s',
    ];

    protected $appends = ['cover_image_url'];

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published');
    }

    /**
     * The Page Builder's `events` section defaults to upcoming_only=true -
     * this scope is what that resolves against, so "upcoming" is defined
     * once here rather than reimplemented per section.
     */
    public function scopeUpcoming(Builder $query): Builder
    {
        return $query->where('start_date', '>=', now()->toDateString())
            ->orderBy('start_date');
    }
}
