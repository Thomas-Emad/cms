<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Menu editing uses a "replace the whole structure" approach rather than
 * granular category/item CRUD endpoints: the admin edits categories and
 * items together in one screen and saves once. UpdateMenuAction (called
 * from the controller) diffs this against existing rows inside a
 * transaction. Simpler for both the admin UX and the API surface than
 * five separate nested-resource endpoints for what's fundamentally one
 * document a restaurant manager edits at a sitting.
 */
class UpdateMenuRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('manageMenu', $this->route('restaurant'));
    }

    public function rules(): array
    {
        return [
            'categories' => ['required', 'array', 'min:1'],
            'categories.*.id' => ['nullable', 'integer'], // present = existing row, absent = new
            'categories.*.name' => ['required', 'string', 'max:255'],
            'categories.*.items' => ['array'],
            'categories.*.items.*.id' => ['nullable', 'integer'],
            'categories.*.items.*.name' => ['required', 'string', 'max:255'],
            'categories.*.items.*.description' => ['nullable', 'string'],
            'categories.*.items.*.price' => ['required', 'numeric', 'min:0'],
            'categories.*.items.*.dietary_info' => ['nullable', 'array'],
            'categories.*.items.*.is_available' => ['boolean'],
        ];
    }
}
