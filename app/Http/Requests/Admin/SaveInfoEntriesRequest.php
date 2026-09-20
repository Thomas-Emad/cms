<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class SaveInfoEntriesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return in_array($this->user()?->role, ['super_admin', 'hotel_admin', 'hotel_staff'], true);
    }

    public function rules(): array
    {
        return [
            // `present` (not `required`) so saving an empty list is allowed - it clears the list.
            'entries' => ['present', 'array', 'max:200'],
            'entries.*.id' => ['nullable', 'integer'],
            'entries.*.group' => ['nullable', 'string', 'max:100'],
            'entries.*.label' => ['required', 'string', 'max:100'],
            'entries.*.value' => ['required', 'string', 'max:100'],
        ];
    }
}
