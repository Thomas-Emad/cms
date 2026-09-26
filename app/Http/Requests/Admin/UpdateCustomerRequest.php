<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->isSuperAdmin();
    }

    public function rules(): array
    {
        $hotelId = $this->route('hotel')?->id;

        return [
            // Hotel Information
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'alpha_dash', Rule::unique('hotels', 'slug')->ignore($hotelId)],
            'domain' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('hotels', 'domain')->ignore($hotelId),
                Rule::unique('hotel_branches', 'domain'),
                'regex:/^[a-zA-Z0-9]([a-zA-Z0-9\-]{0,61}[a-zA-Z0-9])?(\.[a-zA-Z0-9]([a-zA-Z0-9\-]{0,61}[a-zA-Z0-9])?)*$/',
            ],
            'status' => ['required', Rule::in(['active', 'suspended', 'draft'])],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:255'],
            'timezone' => ['nullable', 'string', 'max:100'],
            'currency' => ['nullable', 'string', 'max:3'],

            // Configuration
            'default_locale' => ['nullable', Rule::in(['en', 'ar'])],
            'checkin_time' => ['nullable', 'string', 'max:20'],
            'checkout_time' => ['nullable', 'string', 'max:20'],
        ];
    }
}
