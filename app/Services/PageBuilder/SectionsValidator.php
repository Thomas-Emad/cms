<?php

namespace App\Services\PageBuilder;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

/**
 * Runs on every draft save (see SaveDraftAction). This - not the frontend
 * registry's editorFields - is the actual security boundary: an unknown
 * section `type`, or a prop that fails its definition's schema, is
 * rejected here regardless of what the client sent.
 */
class SectionsValidator
{
    /**
     * @throws ValidationException
     */
    public function validate(array $sections): void
    {
        foreach ($sections as $index => $section) {
            if (! isset($section['id'], $section['type'])) {
                throw ValidationException::withMessages([
                    "sections.{$index}" => 'Each section requires an id and type.',
                ]);
            }

            if (! SectionRegistry::has($section['type'])) {
                throw ValidationException::withMessages([
                    "sections.{$index}.type" => "Unknown section type: {$section['type']}",
                ]);
            }

            $definition = SectionRegistry::get($section['type']);

            Validator::make(
                $section['props'] ?? [],
                $definition->propsSchema()
            )->validate();
        }
    }
}
