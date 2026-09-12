<?php

namespace Tests\Feature;

use App\Services\PageBuilder\SectionRegistry;
use Tests\TestCase;

class SectionRegistryCompletenessTest extends TestCase
{
    private const EXPECTED_TYPES = [
        'hero', 'text', 'image', 'gallery',
        'facility-grid', 'restaurant-grid', 'service-grid',
        'events', 'offers', 'experiences',
        'cta', 'spacer',
    ];

    /** @test */
    public function all_twelve_section_types_are_registered(): void
    {
        $this->assertCount(12, SectionRegistry::types());

        foreach (self::EXPECTED_TYPES as $type) {
            $this->assertTrue(SectionRegistry::has($type), "Missing section type: {$type}");
        }
    }

    /** @test */
    public function every_registered_definition_returns_consistent_metadata(): void
    {
        foreach (SectionRegistry::types() as $type) {
            $definition = SectionRegistry::get($type);

            $this->assertSame($type, $definition->type(), "type() mismatch for registry key '{$type}'");
            $this->assertIsArray($definition->propsSchema());
            $this->assertIsArray($definition->defaultProps());
            $this->assertIsBool($definition->isDynamic());
        }
    }

    /** @test */
    public function default_props_satisfy_each_sections_own_validation_schema(): void
    {
        // A section's defaultProps must be valid enough to actually use -
        // if defaultProps() ever drifted from propsSchema(), a freshly
        // added section in the Builder would fail validation the instant
        // it was saved with zero edits, which would be a real bug.
        foreach (SectionRegistry::types() as $type) {
            $definition = SectionRegistry::get($type);

            $validator = \Illuminate\Support\Facades\Validator::make(
                $definition->defaultProps(),
                $definition->propsSchema()
            );

            $this->assertFalse(
                $validator->fails(),
                "defaultProps() for '{$type}' fails its own propsSchema(): " . json_encode($validator->errors()->all())
            );
        }
    }

    /** @test */
    public function unknown_section_type_is_rejected_by_has_and_throws_on_get(): void
    {
        $this->assertFalse(SectionRegistry::has('not-a-real-section'));

        $this->expectException(\InvalidArgumentException::class);
        SectionRegistry::get('not-a-real-section');
    }
}
