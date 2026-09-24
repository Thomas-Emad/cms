<?php

namespace App\Http\Requests\Admin;

use App\Models\Offer;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class StoreOfferRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Offer::class);
    }

    public function rules(): array
    {
        $offerId = $this->route('offer')?->id;

        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => [
                'required', 'string', 'max:255', 'alpha_dash',
                Rule::unique('offers', 'slug')->where('hotel_id', Auth::user()->hotel_id)->ignore($offerId),
            ],
            'description' => ['nullable', 'string'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'discount' => ['nullable', 'integer', 'min:0', 'max:100'],
            'valid_from' => ['nullable', 'date'],
            // Domain-specific: validity window must make sense, and an
            // offer with no valid_until is treated as always-active (see
            // Offer::scopeActive), so this rule only fires when both are set.
            'valid_until' => ['nullable', 'date', 'after_or_equal:valid_from'],
            'booking_url' => ['nullable', 'url', 'max:255'],
            'status' => ['required', Rule::in(['draft', 'published', 'expired', 'archived'])],
            'featured' => ['boolean'],
            'translations' => ['nullable', 'array'],
            'translations.*' => ['nullable', 'array'],
        ];
    }
}
