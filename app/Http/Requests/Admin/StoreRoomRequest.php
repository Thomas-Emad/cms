<?php

namespace App\Http\Requests\Admin;

use App\Models\Room;
use App\Services\Tenancy\CurrentHotel;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StoreRoomRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Room::class);
    }

    /** Slug is optional in the form; derive it from the name when left blank. */
    protected function prepareForValidation(): void
    {
        if (blank($this->input('slug')) && filled($this->input('name'))) {
            $this->merge(['slug' => Str::slug($this->input('name'))]);
        }
    }

    public function rules(): array
    {
        $roomId = $this->route('room')?->id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => [
                'required', 'string', 'max:255', 'alpha_dash',
                Rule::unique('rooms', 'slug')
                    ->where('hotel_id', app(CurrentHotel::class)->id())
                    ->ignore($roomId),
            ],
            'short_description' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'size_sqm' => ['nullable', 'integer', 'min:1', 'max:5000'],
            'max_guests' => ['nullable', 'integer', 'min:1', 'max:20'],
            'bed_type' => ['nullable', 'string', 'max:100'],
            'view' => ['nullable', 'string', 'max:100'],
            'features' => ['nullable', 'array', 'max:40'],
            'features.*' => ['string', 'max:100'],
            'status' => ['required', Rule::in(['draft', 'published', 'archived'])],
            'featured' => ['boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'translations' => ['nullable', 'array'],
            'translations.*' => ['nullable', 'array'],
        ];
    }
}
