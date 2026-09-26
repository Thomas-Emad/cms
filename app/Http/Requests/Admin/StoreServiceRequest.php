<?php

namespace App\Http\Requests\Admin;

use App\Models\Service;
use App\Services\Tenancy\CurrentHotel;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreServiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Service::class);
    }

    public function rules(): array
    {
        $serviceId = $this->route('service')?->id;

        return [
            'hotel_branch_id' => [
                'nullable', 'integer',
                Rule::exists('hotel_branches', 'id')->where('hotel_id', app(CurrentHotel::class)->id() ?? 0),
            ],
            'name' => ['required', 'string', 'max:255'],
            'slug' => [
                'required', 'string', 'max:255', 'alpha_dash',
                Rule::unique('services', 'slug')->where('hotel_id', app(CurrentHotel::class)->id())->ignore($serviceId),
            ],
            'description' => ['nullable', 'string'],
            'icon' => ['nullable', 'string', 'max:100'],
            'availability' => ['nullable', 'array'],
            'contact' => ['nullable', 'string', 'max:255'],
            'price' => ['nullable', 'numeric', 'min:0'], // null = complimentary/on request
            'request_enabled' => ['boolean'],
            'status' => ['required', Rule::in(['draft', 'published', 'archived'])],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'translations' => ['nullable', 'array'],
            'translations.*' => ['nullable', 'array'],
        ];
    }
}
