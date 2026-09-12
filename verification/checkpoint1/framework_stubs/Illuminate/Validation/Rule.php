<?php
namespace Illuminate\Validation;

/**
 * Minimal stand-in for Laravel's Rule::in(), used only so
 * FacilityGridSectionDefinition::propsSchema() can be called outside a
 * full Laravel install to verify its KEYS are correct. Not a real
 * validation implementation - the genuine Illuminate\Validation\Rule runs
 * wherever the full framework is installed (php artisan test).
 */
class Rule
{
    public static function in(array $values): string
    {
        return 'in:' . implode(',', $values);
    }
}
