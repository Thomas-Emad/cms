<?php

namespace App\Models;

use App\Models\Concerns\FormatsDates;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HotelSettings extends Model
{
    use FormatsDates;

    protected $fillable = [
        'hotel_id', 'checkin_time', 'checkout_time', 'default_locale', 'guest_view', 'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function hotel(): BelongsTo
    {
        return $this->belongsTo(Hotel::class);
    }
}
