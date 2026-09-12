<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class PageSectionsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('page'));
    }

    public function rules(): array
    {
        return [
            'sections' => ['present', 'array'],
            'sections.*.id' => ['required', 'string'],
            'sections.*.type' => ['required', 'string'],
            'sections.*.props' => ['array'],
            'sections.*.settings' => ['array'],
        ];
    }
}
