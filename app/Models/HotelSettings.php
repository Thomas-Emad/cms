<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HotelSettings extends Model
{
    protected $fillable = [
        'hotel_id', 'checkin_time', 'checkout_time', 'default_locale', 'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function hotel(): BelongsTo
    {
        return $this->belongsTo(Hotel::class);
    }
}
