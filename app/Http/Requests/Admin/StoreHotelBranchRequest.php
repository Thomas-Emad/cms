<?php

namespace App\Http\Requests\Admin;

use App\Models\HotelBranch;
use App\Services\Tenancy\CurrentHotel;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StoreHotelBranchRequest extends FormRequest
{
    public function authorize(): bool
    {
        if ($branch = $this->route('branch')) {
            return $this->user()->can('update', $branch);
        }

        return $this->user()->can('create', HotelBranch::class);
    }

    protected function prepareForValidation(): void
    {
        if (blank($this->input('slug')) && filled($this->input('name'))) {
            $this->merge(['slug' => Str::slug($this->input('name'))]);
        }

        if ($this->has('is_main')) {
            $this->merge(['is_main' => filter_var($this->input('is_main'), FILTER_VALIDATE_BOOLEAN)]);
        }

        if ($this->has('domain')) {
            $domain = strtolower(trim((string) $this->input('domain')));
            $this->merge(['domain' => $domain !== '' ? $domain : null]);
        }

        if ($this->has('guest_layout')) {
            $guestLayout = trim((string) $this->input('guest_layout'));
            $this->merge(['guest_layout' => in_array($guestLayout, ['classic', 'tv'], true) ? $guestLayout : null]);
        }
    }

    public function rules(): array
    {
        $branchId = $this->route('branch')?->id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => [
                'required', 'string', 'max:255', 'alpha_dash',
                Rule::unique('hotel_branches', 'slug')
                    ->where('hotel_id', app(CurrentHotel::class)->id())
                    ->ignore($branchId),
            ],
            'domain' => [
                'nullable',
                'string',
                'max:255',
                'regex:/^[a-zA-Z0-9]([a-zA-Z0-9\-]{0,61}[a-zA-Z0-9])?(\.[a-zA-Z0-9]([a-zA-Z0-9\-]{0,61}[a-zA-Z0-9])?)*$/',
                Rule::unique('hotel_branches', 'domain')->ignore($branchId),
                Rule::unique('hotels', 'domain'),
            ],
            'city' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:500'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'short_description' => ['nullable', 'string', 'max:500'],
            'description' => ['nullable', 'string'],
            'cover_image_url' => ['nullable', 'string', 'max:1000'],
            'gallery_urls' => ['nullable', 'array', 'max:50'],
            'gallery_urls.*' => ['string', 'max:1000'],
            'features' => ['nullable', 'array', 'max:40'],
            'features.*' => ['string', 'max:100'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'status' => ['required', Rule::in(['draft', 'published', 'archived'])],
            'guest_layout' => ['nullable', 'string', Rule::in(['classic', 'tv'])],
            'is_main' => ['boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'translations' => ['nullable', 'array'],
            'translations.*' => ['nullable', 'array'],
        ];
    }
}
