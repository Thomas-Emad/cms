<?php

namespace App\Http\Requests\Admin;

use App\Models\Facility;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class StoreFacilityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Facility::class);
    }

    public function rules(): array
    {
        $facilityId = $this->route('facility')?->id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => [
                'required', 'string', 'max:255', 'alpha_dash',
                Rule::unique('facilities', 'slug')
                    ->where('hotel_id', Auth::user()->hotel_id)
                    ->ignore($facilityId),
            ],
            'description' => ['nullable', 'string'],
            'short_description' => ['nullable', 'string', 'max:255'],
            'category' => ['required', Rule::in([
                'wellness', 'fitness', 'pool', 'kids', 'business', 'beach', 'meeting', 'other',
            ])],
            'building' => ['nullable', 'string', 'max:100'],
            'floor' => ['nullable', 'string', 'max:100'],
            'wing' => ['nullable', 'string', 'max:100'],
            'pos_x' => ['nullable', 'numeric'],
            'pos_y' => ['nullable', 'numeric'],
            'opening_hours' => ['nullable', 'array'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'amenities' => ['nullable', 'array'],
            'amenities.*' => ['string'],
            'status' => ['required', Rule::in(['draft', 'published', 'archived'])],
            'featured' => ['boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'translations' => ['nullable', 'array'],
            'translations.*' => ['nullable', 'array'],
            // Cover/gallery images are handled separately via a dedicated
            // media upload endpoint (not built this pass - see README),
            // not as fields on this request.
        ];
    }
}
