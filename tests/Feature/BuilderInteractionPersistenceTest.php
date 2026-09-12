<?php

namespace Tests\Feature;

use App\Actions\Pages\CreatePageAction;
use App\Models\Hotel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Checkpoint 3 adds no new backend architecture (no new routes, no new
 * validation rules) - reorder/duplicate/delete are purely frontend store
 * operations that produce a plain sections array, saved through the EXACT
 * SAME draft endpoint checkpoint 1/2 already built and tested. These
 * tests confirm that endpoint correctly persists the kinds of payloads
 * the interaction layer now produces (reordered arrays, duplicated
 * sections with distinct ids, arrays with sections removed).
 *
 * As with every previous checkpoint: not executed end-to-end here (no
 * Composer/Packagist access in this sandbox to install Laravel) - see
 * verification/README.md for what was independently verified against
 * real MySQL instead.
 */
class BuilderInteractionPersistenceTest extends TestCase
{
    use RefreshDatabase;

    protected Hotel $hotel;
    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->hotel = Hotel::create(['name' => 'Test Hotel', 'slug' => 'test-hotel-' . uniqid(), 'status' => 'active']);
        $this->admin = User::create([
            'hotel_id' => $this->hotel->id, 'role' => 'hotel_admin', 'status' => 'active',
            'name' => 'Admin', 'email' => 'admin-' . uniqid() . '@example.com', 'password' => Hash::make('password'),
        ]);
    }

    /** @test */
    public function a_reordered_sections_array_is_persisted_in_the_new_order(): void
    {
        $page = app(CreatePageAction::class)->execute($this->hotel, ['name' => 'Homepage', 'slug' => '', 'is_home' => true], [
            ['id' => 'a', 'type' => 'hero', 'props' => ['title' => 'A'], 'settings' => []],
            ['id' => 'b', 'type' => 'text', 'props' => ['heading' => 'B'], 'settings' => []],
            ['id' => 'c', 'type' => 'facility-grid', 'props' => ['limit' => 6], 'settings' => []],
        ]);

        // Simulate what the Builder's setSectionsOrder produces: same
        // three sections, reordered (c, a, b).
        $reordered = [
            ['id' => 'c', 'type' => 'facility-grid', 'props' => ['limit' => 6], 'settings' => []],
            ['id' => 'a', 'type' => 'hero', 'props' => ['title' => 'A'], 'settings' => []],
            ['id' => 'b', 'type' => 'text', 'props' => ['heading' => 'B'], 'settings' => []],
        ];

        $response = $this->actingAs($this->admin)->putJson("/admin/pages/{$page->id}/draft", ['sections' => $reordered]);
        $response->assertRedirect();

        $page->refresh();
        $saved = $page->draftVersion->sections['sections'];

        $this->assertEquals(['c', 'a', 'b'], array_column($saved, 'id'));
    }

    /** @test */
    public function a_duplicated_section_with_a_new_id_is_persisted_alongside_the_original(): void
    {
        $page = app(CreatePageAction::class)->execute($this->hotel, ['name' => 'Homepage', 'slug' => '', 'is_home' => true], [
            ['id' => 'text-original', 'type' => 'text', 'props' => ['heading' => 'Original'], 'settings' => []],
        ]);

        // Simulate what store.duplicateSection() produces: original
        // unchanged, plus a new section with a distinct id and deep-copied props.
        $withDuplicate = [
            ['id' => 'text-original', 'type' => 'text', 'props' => ['heading' => 'Original'], 'settings' => []],
            ['id' => 'text-abc123', 'type' => 'text', 'props' => ['heading' => 'Original'], 'settings' => []],
        ];

        $response = $this->actingAs($this->admin)->putJson("/admin/pages/{$page->id}/draft", ['sections' => $withDuplicate]);
        $response->assertRedirect();

        $page->refresh();
        $saved = $page->draftVersion->sections['sections'];

        $this->assertCount(2, $saved);
        $this->assertNotEquals($saved[0]['id'], $saved[1]['id']);
        $this->assertEquals($saved[0]['props']['heading'], $saved[1]['props']['heading']);
    }

    /** @test */
    public function a_deleted_section_is_absent_after_save_and_reload(): void
    {
        $page = app(CreatePageAction::class)->execute($this->hotel, ['name' => 'Homepage', 'slug' => '', 'is_home' => true], [
            ['id' => 'hero-1', 'type' => 'hero', 'props' => ['title' => 'Keep me'], 'settings' => []],
            ['id' => 'text-1', 'type' => 'text', 'props' => ['heading' => 'Delete me'], 'settings' => []],
        ]);

        // Simulate store.removeSection('text-1') - just the remaining array is sent.
        $afterDelete = [
            ['id' => 'hero-1', 'type' => 'hero', 'props' => ['title' => 'Keep me'], 'settings' => []],
        ];

        $this->actingAs($this->admin)->putJson("/admin/pages/{$page->id}/draft", ['sections' => $afterDelete]);

        $reload = $this->actingAs($this->admin)->get("/admin/pages/{$page->id}/builder");

        $reload->assertInertia(fn ($assert) => $assert
            ->has('sections', 1)
            ->where('sections.0.id', 'hero-1')
        );
    }

    /** @test */
    public function a_reordered_and_duplicated_payload_still_fails_validation_if_it_contains_an_invalid_section_type(): void
    {
        $page = app(CreatePageAction::class)->execute($this->hotel, ['name' => 'Homepage', 'slug' => '', 'is_home' => true]);

        // Interaction-layer operations don't bypass the existing
        // SectionsValidator - an invalid type anywhere in the payload
        // still rejects the whole save, exactly as in checkpoint 1/2.
        $response = $this->actingAs($this->admin)->putJson("/admin/pages/{$page->id}/draft", [
            'sections' => [
                ['id' => 'hero-1', 'type' => 'hero', 'props' => ['title' => 'Fine'], 'settings' => []],
                ['id' => 'hero-1-copy', 'type' => 'not-a-real-section', 'props' => [], 'settings' => []],
            ],
        ]);

        $response->assertStatus(422);
    }
}
