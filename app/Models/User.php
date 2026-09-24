<?php

namespace App\Models;

use App\Models\Concerns\FormatsDates;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use FormatsDates, HasFactory, Notifiable;

    protected $fillable = [
        'hotel_id', 'role', 'status', 'name', 'email', 'password',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function hotel(): BelongsTo
    {
        return $this->belongsTo(Hotel::class);
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    public function isHotelAdmin(): bool
    {
        return $this->role === 'hotel_admin';
    }

    public function isHotelStaff(): bool
    {
        return $this->role === 'hotel_staff';
    }
}
