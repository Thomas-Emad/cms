<?php

namespace App\Http\Requests\Admin;

use App\Models\Restaurant;
use App\Services\Tenancy\CurrentHotel;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRestaurantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Restaurant::class);
    }

    public function rules(): array
    {
        $restaurantId = $this->route('restaurant')?->id;

        return [
            'hotel_branch_id' => [
                'nullable', 'integer',
                Rule::exists('hotel_branches', 'id')->where('hotel_id', app(CurrentHotel::class)->id() ?? 0),
            ],
            'name' => ['required', 'string', 'max:255'],
            'slug' => [
                'required', 'string', 'max:255', 'alpha_dash',
                Rule::unique('restaurants', 'slug')
                    ->where('hotel_id', app(CurrentHotel::class)->id())
                    ->ignore($restaurantId),
            ],
            'description' => ['nullable', 'string'],
            'cuisine' => ['nullable', 'string', 'max:100'],
            'location' => ['nullable', 'string', 'max:255'],
            'floor' => ['nullable', 'string', 'max:100'],
            'opening_hours' => ['nullable', 'array'],
            'dress_code' => ['nullable', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:50'],
            'reservation_url' => ['nullable', 'url', 'max:255'],
            'status' => ['required', Rule::in(['draft', 'published', 'archived'])],
            'featured' => ['boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'translations' => ['nullable', 'array'],
            'translations.*' => ['nullable', 'array'],
        ];
    }
}
