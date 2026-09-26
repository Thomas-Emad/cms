<?php

namespace App\Http\Requests\Admin;

use App\Models\Experience;
use App\Services\Tenancy\CurrentHotel;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreExperienceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Experience::class);
    }

    public function rules(): array
    {
        $experienceId = $this->route('experience')?->id;

        return [
            'hotel_branch_id' => [
                'nullable', 'integer',
                Rule::exists('hotel_branches', 'id')->where('hotel_id', app(CurrentHotel::class)->id() ?? 0),
            ],
            'title' => ['required', 'string', 'max:255'],
            'slug' => [
                'required', 'string', 'max:255', 'alpha_dash',
                Rule::unique('experiences', 'slug')->where('hotel_id', app(CurrentHotel::class)->id())->ignore($experienceId),
            ],
            'description' => ['nullable', 'string'],
            'category' => ['nullable', 'string', 'max:100'],
            'duration' => ['nullable', 'string', 'max:100'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'booking_url' => ['nullable', 'url', 'max:255'],
            'status' => ['required', Rule::in(['draft', 'published', 'archived'])],
            'featured' => ['boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'translations' => ['nullable', 'array'],
            'translations.*' => ['nullable', 'array'],
        ];
    }
}
