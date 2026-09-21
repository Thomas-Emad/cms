<?php

namespace App\Models;

use App\Models\Concerns\BelongsToHotel;
use Illuminate\Database\Eloquent\Model;

/** The hotel's indoor map (floors, nodes, locations) as one validated JSON document. */
class HotelMap extends Model
{
    use BelongsToHotel;

    protected $fillable = ['hotel_id', 'data'];

    protected $casts = ['data' => 'array'];
}
