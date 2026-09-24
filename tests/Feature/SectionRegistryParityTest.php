<?php

namespace Tests\Feature;

use App\Services\PageBuilder\SectionRegistry;
use Tests\TestCase;

/**
 * PARITY MECHANISM (documented here, and in resources/js/PageBuilder/
 * section-types.json's presence itself):
 *
 * resources/js/PageBuilder/section-types.json is the single canonical
 * list of section types this project supports. It is NOT generated from
 * either registry - it's a hand-maintained manifest both sides are tested
 * against independently:
 *   - This test (backend): SectionRegistry::types() must exactly match it.
 *   - registry.test.ts (frontend): Object.keys(SECTION_REGISTRY) must
 *     exactly match the SAME physical file (imported directly, since Vite
 *     supports JSON imports - no duplication of the list).
 *
 * Why a manifest rather than one side generating the other: PHP and
 * TypeScript don't share a runtime, and this project has no build step
 * that runs both together, so there's no clean way for one registry to
 * literally read the other at test time. A manifest file is the standard,
 * deterministic way to declare "this is the agreed contract" without
 * either language depending on the other's toolchain. Both sides failing
 * to match it is a hard test failure, not a warning - there is no
 * "graceful drift" mode.
 *
 * Practical effect: adding a 13th section type requires touching THREE
 * places (a new backend SectionDefinition + registry line, a new frontend
 * registry.ts entry, and this manifest file) - if any one is missed, the
 * corresponding test in that language fails immediately, on the next test
 * run, rather than silently shipping a section type usable in one context
 * (e.g. saved via a script) but broken in the other (e.g. can't be added
 * via the Builder UI, or a saved section with that type fails to render).
 */
class SectionRegistryParityTest extends TestCase
{
    /** @test */
    public function backend_registry_exactly_matches_the_canonical_manifest(): void
    {
        $manifestPath = resource_path('js/PageBuilder/section-types.json');
        $this->assertFileExists($manifestPath, 'Canonical section-types.json manifest is missing.');

        $manifest = json_decode(file_get_contents($manifestPath), true);
        $this->assertIsArray($manifest);

        $backendTypes = SectionRegistry::types();

        sort($manifest);
        sort($backendTypes);

        $this->assertSame(
            $manifest,
            $backendTypes,
            "Backend SectionRegistry and section-types.json have diverged.\n".
            'In manifest but not backend: '.json_encode(array_values(array_diff($manifest, $backendTypes)))."\n".
            'In backend but not manifest: '.json_encode(array_values(array_diff($backendTypes, $manifest)))
        );
    }

    /** @test */
    public function the_manifest_itself_has_no_duplicate_entries(): void
    {
        $manifestPath = resource_path('js/PageBuilder/section-types.json');
        $manifest = json_decode(file_get_contents($manifestPath), true);

        $this->assertSame(
            count($manifest),
            count(array_unique($manifest)),
            'section-types.json contains duplicate entries.'
        );
    }
}
