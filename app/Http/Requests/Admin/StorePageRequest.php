<?php

namespace App\Http\Requests\Admin;

use App\Models\Page;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class StorePageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Page::class);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            // Lowercase letters, numbers, and hyphens only - matches the
            // same slug convention every Phase 2 entity uses. Empty
            // string is deliberately allowed here too: that's the
            // homepage's slug (see Page model / CreatePageAction), and
            // is_home governs that case rather than a separate field.
            'slug' => [
                'nullable', 'string', 'max:255', 'regex:/^[a-z0-9]+(-[a-z0-9]+)*$/',
                Rule::unique('pages', 'slug')->where('hotel_id', Auth::user()->hotel_id),
            ],
            'is_home' => ['boolean'],
            // 'scroll' = normal page (default); 'fullscreen' = no page
            // scroll, sections fill the viewport (Smart-TV-style home
            // screens). See migration 2026_09_21_000001.
            'layout' => ['nullable', 'string', Rule::in(['scroll', 'fullscreen'])],
        ];
    }

    public function messages(): array
    {
        return [
            'slug.regex' => 'Slug may only contain lowercase letters, numbers, and hyphens.',
            'slug.unique' => 'A page with this slug already exists.',
        ];
    }

    /**
     * Normalizes the payload BEFORE validation runs - a home page's slug
     * is always '' regardless of what was typed (a home page's URL is
     * always "/", so a distinct-looking slug for it would be confusing),
     * and this must happen before the `unique` rule below checks the
     * slug's value, not after (passedValidation() would be too late and
     * would validate whatever was originally typed instead).
     */
    protected function prepareForValidation(): void
    {
        if ($this->boolean('is_home')) {
            $this->merge(['slug' => '']);
        }
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if (! $this->boolean('is_home') && blank($this->input('slug'))) {
                $validator->errors()->add('slug', 'Slug is required unless this is the home page.');
            }
        });
    }
}
