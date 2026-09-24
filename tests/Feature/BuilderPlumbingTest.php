<?php

namespace Tests\Feature;

use App\Actions\Pages\CreatePageAction;
use App\Actions\Pages\PublishPageAction;
use App\Models\Facility;
use App\Models\Hotel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * NOTE: as with checkpoint 1, I could not execute this suite end-to-end in
 * the sandbox (no Composer/Packagist access to install Laravel itself -
 * see verification/README.md). What I DID verify directly against real
 * MySQL + PHP is documented in verification/ alongside this file. These
 * tests are written to the same standard as checkpoint 1's and should be
 * run via `php artisan test` in a real environment before being trusted.
 */
class BuilderPlumbingTest extends TestCase
{
    use RefreshDatabase;

    protected Hotel $hotel;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->hotel = Hotel::create(['name' => 'Test Hotel', 'slug' => 'test-hotel-'.uniqid(), 'status' => 'active']);
        $this->admin = User::create([
            'hotel_id' => $this->hotel->id, 'role' => 'hotel_admin', 'status' => 'active',
            'name' => 'Admin', 'email' => 'admin-'.uniqid().'@example.com', 'password' => Hash::make('password'),
        ]);
    }

    /** @test */
    public function the_builder_loads_the_pages_current_draft_version(): void
    {
        $page = app(CreatePageAction::class)->execute($this->hotel, [
            'name' => 'Homepage', 'slug' => '', 'is_home' => true,
        ], [
            ['id' => 'hero-1', 'type' => 'hero', 'props' => ['title' => 'Draft Title'], 'settings' => []],
        ]);

        $response = $this->actingAs($this->admin)->get("/admin/pages/{$page->id}/builder");

        $response->assertOk();
        $response->assertInertia(fn ($assert) => $assert
            ->component('Admin/Pages/Builder')
            ->where('sections.0.props.title', 'Draft Title')
            ->where('page.id', $page->id)
        );
    }

    /** @test */
    public function the_builder_never_loads_the_published_version_even_after_publishing(): void
    {
        $page = app(CreatePageAction::class)->execute($this->hotel, [
            'name' => 'Homepage', 'slug' => '', 'is_home' => true,
        ], [
            ['id' => 'hero-1', 'type' => 'hero', 'props' => ['title' => 'Published Title'], 'settings' => []],
        ]);

        app(PublishPageAction::class)->execute($page->fresh());

        // Edit the draft AFTER publishing.
        $this->actingAs($this->admin)->putJson("/admin/pages/{$page->id}/draft", [
            'sections' => [
                ['id' => 'hero-1', 'type' => 'hero', 'props' => ['title' => 'Newer Draft Title'], 'settings' => []],
            ],
        ]);

        $response = $this->actingAs($this->admin)->get("/admin/pages/{$page->id}/builder");

        // The Builder must show the DRAFT's latest content, not the
        // published snapshot from before this edit.
        $response->assertInertia(fn ($assert) => $assert
            ->where('sections.0.props.title', 'Newer Draft Title')
        );
    }

    /** @test */
    public function adding_and_saving_sections_preserves_valid_json_on_reload(): void
    {
        $page = app(CreatePageAction::class)->execute($this->hotel, [
            'name' => 'Homepage', 'slug' => '', 'is_home' => true,
        ]);

        $sections = [
            ['id' => 'hero-1', 'type' => 'hero', 'props' => ['title' => 'Welcome'], 'settings' => ['padding' => 'large']],
            ['id' => 'text-1', 'type' => 'text', 'props' => ['heading' => 'About', 'body' => 'Some text.'], 'settings' => []],
            ['id' => 'fg-1', 'type' => 'facility-grid', 'props' => ['category' => 'wellness', 'featured_only' => true, 'limit' => 4, 'columns' => 3], 'settings' => []],
        ];

        $saveResponse = $this->actingAs($this->admin)->put("/admin/pages/{$page->id}/draft", ['sections' => $sections]);
        $saveResponse->assertRedirect();

        $reloadResponse = $this->actingAs($this->admin)->get("/admin/pages/{$page->id}/builder");

        $reloadResponse->assertInertia(fn ($assert) => $assert
            ->has('sections', 3)
            ->where('sections.0.type', 'hero')
            ->where('sections.1.type', 'text')
            ->where('sections.2.props.category', 'wellness')
            ->where('sections.2.props.limit', 4)
        );
    }

    /** @test */
    public function invalid_section_types_cannot_be_saved_via_the_builders_save_endpoint(): void
    {
        $page = app(CreatePageAction::class)->execute($this->hotel, ['name' => 'Homepage', 'slug' => '', 'is_home' => true]);

        $response = $this->actingAs($this->admin)->putJson("/admin/pages/{$page->id}/draft", [
            'sections' => [
                ['id' => 's1', 'type' => 'raw-html-injector', 'props' => ['html' => '<script>alert(1)</script>'], 'settings' => []],
            ],
        ]);

        $response->assertStatus(422);

        // Confirm the invalid section was NOT persisted despite the request.
        $page->refresh();
        $draftSections = $page->draftVersion->sections['sections'] ?? [];
        $this->assertEmpty($draftSections);
    }

    /** @test */
    public function invalid_props_for_a_known_section_type_are_also_rejected(): void
    {
        $page = app(CreatePageAction::class)->execute($this->hotel, ['name' => 'Homepage', 'slug' => '', 'is_home' => true]);

        // facility-grid's schema restricts columns to [2,3,4].
        $response = $this->actingAs($this->admin)->putJson("/admin/pages/{$page->id}/draft", [
            'sections' => [
                ['id' => 'fg-1', 'type' => 'facility-grid', 'props' => ['columns' => 99, 'limit' => 6], 'settings' => []],
            ],
        ]);

        $response->assertStatus(422);
    }

    /** @test */
    public function a_hotel_admin_cannot_open_another_hotels_page_in_the_builder(): void
    {
        $otherHotel = Hotel::create(['name' => 'Other Hotel', 'slug' => 'other-hotel-'.uniqid(), 'status' => 'active']);
        $otherPage = app(CreatePageAction::class)->execute($otherHotel, ['name' => 'Their Homepage', 'slug' => '', 'is_home' => true]);

        $response = $this->actingAs($this->admin)->get("/admin/pages/{$otherPage->id}/builder");

        $response->assertForbidden();
    }

    /** @test */
    public function a_hotel_admin_cannot_save_a_draft_for_another_hotels_page(): void
    {
        $otherHotel = Hotel::create(['name' => 'Other Hotel', 'slug' => 'other-hotel-'.uniqid(), 'status' => 'active']);
        $otherPage = app(CreatePageAction::class)->execute($otherHotel, ['name' => 'Their Homepage', 'slug' => '', 'is_home' => true]);

        $response = $this->actingAs($this->admin)->putJson("/admin/pages/{$otherPage->id}/draft", [
            'sections' => [['id' => 's1', 'type' => 'hero', 'props' => ['title' => 'Hijacked'], 'settings' => []]],
        ]);

        $response->assertForbidden();

        // And the other hotel's draft is provably untouched.
        $otherPage->refresh();
        $this->assertEmpty($otherPage->draftVersion->sections['sections'] ?? []);
    }

    /** @test */
    public function preview_resolves_facility_grid_using_the_latest_saved_draft_props_not_stale_data(): void
    {
        Facility::create(['hotel_id' => $this->hotel->id, 'name' => 'Wellness Spa', 'slug' => 'wellness-spa', 'category' => 'wellness', 'status' => 'published']);
        Facility::create(['hotel_id' => $this->hotel->id, 'name' => 'Fitness Gym', 'slug' => 'fitness-gym', 'category' => 'fitness', 'status' => 'published']);

        $page = app(CreatePageAction::class)->execute($this->hotel, ['name' => 'Homepage', 'slug' => '', 'is_home' => true], [
            ['id' => 'fg-1', 'type' => 'facility-grid', 'props' => ['category' => 'wellness', 'limit' => 10], 'settings' => []],
        ]);

        $firstPreview = $this->actingAs($this->admin)->get("/admin/pages/{$page->id}/preview");
        $firstPreview->assertInertia(fn ($assert) => $assert
            ->where('sections.0.data.facilities.0.name', 'Wellness Spa')
            ->has('sections.0.data.facilities', 1)
        );

        // Change the category and save - simulating the admin editing in
        // the Builder and clicking Save Draft before Preview.
        $this->actingAs($this->admin)->putJson("/admin/pages/{$page->id}/draft", [
            'sections' => [['id' => 'fg-1', 'type' => 'facility-grid', 'props' => ['category' => 'fitness', 'limit' => 10], 'settings' => []]],
        ]);

        $secondPreview = $this->actingAs($this->admin)->get("/admin/pages/{$page->id}/preview");
        $secondPreview->assertInertia(fn ($assert) => $assert
            ->where('sections.0.data.facilities.0.name', 'Fitness Gym')
            ->has('sections.0.data.facilities', 1)
        );
    }

    /** @test */
    public function preview_never_leaks_another_hotels_facilities(): void
    {
        $otherHotel = Hotel::create(['name' => 'Other Hotel', 'slug' => 'other-hotel-'.uniqid(), 'status' => 'active']);
        Facility::create(['hotel_id' => $otherHotel->id, 'name' => 'Other Hotel Spa', 'slug' => 'other-hotel-spa', 'category' => 'wellness', 'status' => 'published']);
        Facility::create(['hotel_id' => $this->hotel->id, 'name' => 'My Hotel Spa', 'slug' => 'my-hotel-spa', 'category' => 'wellness', 'status' => 'published']);

        $page = app(CreatePageAction::class)->execute($this->hotel, ['name' => 'Homepage', 'slug' => '', 'is_home' => true], [
            ['id' => 'fg-1', 'type' => 'facility-grid', 'props' => ['category' => 'wellness', 'limit' => 10], 'settings' => []],
        ]);

        $response = $this->actingAs($this->admin)->get("/admin/pages/{$page->id}/preview");

        $response->assertInertia(fn ($assert) => $assert
            ->has('sections.0.data.facilities', 1)
            ->where('sections.0.data.facilities.0.name', 'My Hotel Spa')
        );
    }

    /** @test */
    public function draft_edits_remain_invisible_to_guests_until_publish_even_after_a_builder_save(): void
    {
        $page = app(CreatePageAction::class)->execute($this->hotel, [
            'name' => 'Homepage', 'slug' => '', 'is_home' => true,
        ], [
            ['id' => 'hero-1', 'type' => 'hero', 'props' => ['title' => 'Original Published Title'], 'settings' => []],
        ]);
        app(PublishPageAction::class)->execute($page->fresh());

        // Save (not publish) a draft edit via the Builder's save endpoint.
        $this->actingAs($this->admin)->putJson("/admin/pages/{$page->id}/draft", [
            'sections' => [['id' => 'hero-1', 'type' => 'hero', 'props' => ['title' => 'Edited But Not Published'], 'settings' => []]],
        ]);

        $guestResponse = $this->get('/');

        $guestResponse->assertInertia(fn ($assert) => $assert
            ->where('sections.0.props.title', 'Original Published Title')
        );
    }
}
