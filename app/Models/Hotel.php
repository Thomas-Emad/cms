<?php

namespace App\Models;

use App\Models\Concerns\HasMedia;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Hotel extends Model
{
    use HasFactory, HasMedia;

    protected $fillable = [
        'name', 'slug', 'domain', 'status',
        'contact_email', 'contact_phone', 'address', 'timezone', 'currency',
    ];

    public function settings(): HasOne
    {
        return $this->hasOne(HotelSettings::class);
    }

    public function themes(): HasMany
    {
        return $this->hasMany(Theme::class);
    }

    public function activeTheme(): HasOne
    {
        return $this->hasOne(Theme::class)->where('is_active', true);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
