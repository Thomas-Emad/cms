<?php

namespace App\Http\Requests\Admin;

use App\Services\Tenancy\CurrentHotel;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateGuestViewRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Authorize against the actual current-hotel INSTANCE, not the
        // Hotel::class string - HotelPolicy::update() is typed to a real
        // Hotel model (it checks $user->hotel_id === $hotel->id), so a
        // class-based can() check would never reach it correctly.
        return $this->user()->can('update', app(CurrentHotel::class)->get());
    }

    public function rules(): array
    {
        return [
            'guest_view' => ['required', 'string', Rule::in(['classic', 'tv'])],
        ];
    }
}
