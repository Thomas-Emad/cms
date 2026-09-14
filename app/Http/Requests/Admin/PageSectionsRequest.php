<?php

namespace App\Http\Requests\Admin;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Http\FormRequest;

class PageSectionsRequest extends FormRequest
{
    /**
     * Generalized for entity-backed presentations: this same request class
     * is used by both /admin/pages/{page}/draft (route param 'page', a
     * Page) and /admin/restaurants/{restaurant}/presentation/draft (route
     * param 'restaurant', a Restaurant) - hardcoding `$this->route('page')`
     * would silently authorize against null for the latter. Finding
     * whichever bound Eloquent model is present in the route works for
     * both without this request needing to know the specific param name,
     * and for any future entity type added the same way.
     */
    public function authorize(): bool
    {
        $model = collect($this->route()->parameters())
            ->first(fn ($param) => $param instanceof Model);

        return $model !== null && $this->user()->can('update', $model);
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
