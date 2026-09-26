<?php

namespace App\Http\Requests\Admin;

use App\Models\Event;
use App\Services\Tenancy\CurrentHotel;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Event::class);
    }

    public function rules(): array
    {
        $eventId = $this->route('event')?->id;

        return [
            'hotel_branch_id' => [
                'nullable', 'integer',
                Rule::exists('hotel_branches', 'id')->where('hotel_id', app(CurrentHotel::class)->id() ?? 0),
            ],
            'title' => ['required', 'string', 'max:255'],
            'slug' => [
                'required', 'string', 'max:255', 'alpha_dash',
                Rule::unique('events', 'slug')->where('hotel_id', app(CurrentHotel::class)->id())->ignore($eventId),
            ],
            'description' => ['nullable', 'string'],
            'start_date' => ['required', 'date'],
            // Domain-specific: an event's end can't precede its start.
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'start_time' => ['nullable', 'date_format:H:i'],
            'end_time' => ['nullable', 'date_format:H:i', 'after:start_time'],
            'location' => ['nullable', 'string', 'max:255'],
            'capacity' => ['nullable', 'integer', 'min:1'],
            'booking_required' => ['boolean'],
            'booking_url' => ['nullable', 'required_if:booking_required,true', 'url', 'max:255'],
            'status' => ['required', Rule::in(['draft', 'published', 'cancelled', 'archived'])],
            'translations' => ['nullable', 'array'],
            'translations.*' => ['nullable', 'array'],
        ];
    }
}
