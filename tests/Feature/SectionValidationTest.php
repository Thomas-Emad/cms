<?php

namespace Tests\Feature;

use App\Actions\Pages\CreatePageAction;
use App\Models\Hotel;
use App\Models\Media;
use App\Models\Page;
use App\Models\User;
use App\Services\PageBuilder\PageRenderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SectionValidationTest extends TestCase
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

    protected function saveSections(array $sections)
    {
        $page = app(CreatePageAction::class)->execute($this->hotel, ['name' => 'Test', 'slug' => 'test-' . uniqid(), 'is_home' => false]);

        return $this->actingAs($this->admin)->putJson("/admin/pages/{$page->id}/draft", ['sections' => $sections]);
    }

    // --- Valid props accepted, for every one of the 12 types ---

    public static function validSectionProvider(): array
    {
        return [
            'hero' => ['hero', ['title' => 'Welcome', 'subtitle' => 'Sub', 'button_text' => 'Go', 'button_url' => '/facilities']],
            'text' => ['text', ['heading' => 'About', 'body' => 'Some text.']],
            'cta' => ['cta', ['heading' => 'Book now', 'button_text' => 'Book', 'button_url' => 'https://example.com']],
            'spacer' => ['spacer', ['height' => 'large']],
            'facility-grid' => ['facility-grid', ['category' => 'wellness', 'featured_only' => true, 'limit' => 6, 'columns' => 3]],
            'restaurant-grid' => ['restaurant-grid', ['cuisine' => 'Mediterranean', 'featured_only' => false, 'limit' => 6]],
            'service-grid' => ['service-grid', ['limit' => 6]],
            'events' => ['events', ['limit' => 4, 'upcoming_only' => true]],
            'offers' => ['offers', ['limit' => 4, 'active_only' => true, 'featured_only' => false]],
            'experiences' => ['experiences', ['category' => 'wellness', 'featured_only' => true, 'limit' => 4]],
        ];
    }

    /** @test @dataProvider validSectionProvider */
    public function valid_props_are_accepted(string $type, array $props): void
    {
        $response = $this->saveSections([
            ['id' => 's1', 'type' => $type, 'props' => $props, 'settings' => []],
        ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();
    }

    /** @test */
    public function image_with_a_valid_tenant_owned_media_id_is_accepted(): void
    {
        $media = Media::create([
            'hotel_id' => $this->hotel->id, 'disk' => 'public', 'path' => 'x.jpg',
            'mediable_type' => 'App\\Models\\Page', 'mediable_id' => 1, 'collection' => 'gallery',
        ]);

        $response = $this->saveSections([
            ['id' => 's1', 'type' => 'image', 'props' => ['media_id' => $media->id, 'caption' => 'A photo'], 'settings' => []],
        ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();
    }

    /** @test */
    public function gallery_with_valid_media_ids_is_accepted(): void
    {
        $m1 = Media::create(['hotel_id' => $this->hotel->id, 'disk' => 'public', 'path' => 'a.jpg', 'mediable_type' => 'x', 'mediable_id' => 1, 'collection' => 'gallery']);
        $m2 = Media::create(['hotel_id' => $this->hotel->id, 'disk' => 'public', 'path' => 'b.jpg', 'mediable_type' => 'x', 'mediable_id' => 1, 'collection' => 'gallery']);

        $response = $this->saveSections([
            ['id' => 's1', 'type' => 'gallery', 'props' => ['title' => 'Photos', 'media_ids' => [$m1->id, $m2->id]], 'settings' => []],
        ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();
    }

    // --- Invalid props rejected ---

    /** @test */
    public function cta_missing_required_heading_is_rejected(): void
    {
        $response = $this->saveSections([
            ['id' => 's1', 'type' => 'cta', 'props' => ['button_text' => 'Go', 'button_url' => '/x'], 'settings' => []],
        ]);

        $response->assertStatus(422);
    }

    /** @test */
    public function spacer_with_an_invalid_height_value_is_rejected(): void
    {
        $response = $this->saveSections([
            ['id' => 's1', 'type' => 'spacer', 'props' => ['height' => 'gigantic'], 'settings' => []],
        ]);

        $response->assertStatus(422);
    }

    /** @test */
    public function facility_grid_with_an_invalid_category_is_rejected(): void
    {
        $response = $this->saveSections([
            ['id' => 's1', 'type' => 'facility-grid', 'props' => ['category' => 'not-a-real-category', 'limit' => 6], 'settings' => []],
        ]);

        $response->assertStatus(422);
    }

    /** @test */
    public function unknown_section_type_is_rejected(): void
    {
        $response = $this->saveSections([
            ['id' => 's1', 'type' => 'raw-html-embed', 'props' => ['html' => '<script>alert(1)</script>'], 'settings' => []],
        ]);

        $response->assertStatus(422);
    }

    // --- Limits are bounded ---

    public static function limitBoundedSectionTypes(): array
    {
        return [
            ['facility-grid', 'limit'],
            ['restaurant-grid', 'limit'],
            ['service-grid', 'limit'],
            ['events', 'limit'],
            ['offers', 'limit'],
            ['experiences', 'limit'],
        ];
    }

    /** @test @dataProvider limitBoundedSectionTypes */
    public function limit_of_zero_is_rejected(string $type, string $limitKey): void
    {
        $response = $this->saveSections([
            ['id' => 's1', 'type' => $type, 'props' => [$limitKey => 0], 'settings' => []],
        ]);

        $response->assertStatus(422);
    }

    /** @test @dataProvider limitBoundedSectionTypes */
    public function limit_above_twelve_is_rejected(string $type, string $limitKey): void
    {
        $response = $this->saveSections([
            ['id' => 's1', 'type' => $type, 'props' => [$limitKey => 500], 'settings' => []],
        ]);

        $response->assertStatus(422);
    }

    /** @test @dataProvider limitBoundedSectionTypes */
    public function limit_of_twelve_the_maximum_is_accepted(string $type, string $limitKey): void
    {
        $response = $this->saveSections([
            ['id' => 's1', 'type' => $type, 'props' => [$limitKey => 12], 'settings' => []],
        ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();
    }

    // --- URLs validated where applicable ---

    /** @test */
    public function image_with_a_malformed_link_url_is_rejected(): void
    {
        $response = $this->saveSections([
            ['id' => 's1', 'type' => 'image', 'props' => ['media_id' => 1, 'link_url' => 'not a url at all'], 'settings' => []],
        ]);

        $response->assertStatus(422);
    }

    /** @test */
    public function image_with_a_valid_url_format_is_accepted(): void
    {
        $media = Media::create(['hotel_id' => $this->hotel->id, 'disk' => 'public', 'path' => 'x.jpg', 'mediable_type' => 'x', 'mediable_id' => 1, 'collection' => 'gallery']);

        $response = $this->saveSections([
            ['id' => 's1', 'type' => 'image', 'props' => ['media_id' => $media->id, 'link_url' => 'https://example.com/page'], 'settings' => []],
        ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();
    }

    // --- Media IDs validated / tenant-safe ---

    /** @test */
    public function image_referencing_a_nonexistent_media_id_is_rejected_at_save_time(): void
    {
        $response = $this->saveSections([
            ['id' => 's1', 'type' => 'image', 'props' => ['media_id' => 999999], 'settings' => []],
        ]);

        $response->assertStatus(422);
    }

    /** @test */
    public function image_referencing_another_hotels_media_id_passes_schema_validation_but_resolves_to_nothing(): void
    {
        // This is the important tenant-safety case: `exists:media,id` only
        // proves the row exists SOMEWHERE, not that it belongs to this
        // hotel - so a media_id belonging to a different tenant will pass
        // schema validation (the id genuinely exists) but MUST resolve to
        // null at render time via ImageSectionDefinition's tenant re-check
        // (ResolvesMedia trait), never leaking the other hotel's file.
        $otherHotel = Hotel::create(['name' => 'Other Hotel', 'slug' => 'other-hotel-' . uniqid(), 'status' => 'active']);
        $otherMedia = Media::create(['hotel_id' => $otherHotel->id, 'disk' => 'public', 'path' => 'secret.jpg', 'mediable_type' => 'x', 'mediable_id' => 1, 'collection' => 'gallery']);

        $saveResponse = $this->saveSections([
            ['id' => 's1', 'type' => 'image', 'props' => ['media_id' => $otherMedia->id], 'settings' => []],
        ]);
        $saveResponse->assertRedirect(); // passes schema validation - the row exists

        $page = Page::where('hotel_id', $this->hotel->id)->latest()->first();
        $resolved = app(PageRenderService::class)->resolveSections(
            $page->draftVersion->sections['sections'],
            $this->hotel
        );

        $this->assertNull($resolved[0]['data']['media'], 'Cross-tenant media leaked into resolved section data!');
    }
}
