<?php

namespace Tests\Feature;

use App\Actions\Pages\CreatePageAction;
use App\Models\Facility;
use App\Models\Hotel;
use App\Models\User;
use App\Services\PageBuilder\PageRenderService;
use App\Services\Tenancy\CurrentHotel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SectionResolutionTest extends TestCase
{
    use RefreshDatabase;

    protected Hotel $hotelA;
    protected Hotel $hotelB;

    protected function setUp(): void
    {
        parent::setUp();

        $this->hotelA = Hotel::create(['name' => 'Hotel A', 'slug' => 'hotel-a-' . uniqid(), 'status' => 'active']);
        $this->hotelB = Hotel::create(['name' => 'Hotel B', 'slug' => 'hotel-b-' . uniqid(), 'status' => 'active']);
    }

    /** @test */
    public function facility_grid_resolves_only_the_current_hotels_facilities(): void
    {
        app()->make(CurrentHotel::class)->set($this->hotelA);
        Facility::create([
            'hotel_id' => $this->hotelA->id, 'name' => 'Hotel A Spa', 'slug' => 'hotel-a-spa',
            'category' => 'wellness', 'status' => 'published',
        ]);

        app()->make(CurrentHotel::class)->set($this->hotelB);
        Facility::create([
            'hotel_id' => $this->hotelB->id, 'name' => 'Hotel B Spa', 'slug' => 'hotel-b-spa',
            'category' => 'wellness', 'status' => 'published',
        ]);

        $service = app(PageRenderService::class);

        app()->make(CurrentHotel::class)->set($this->hotelA);
        $resolved = $service->resolveSections([
            ['id' => 's1', 'type' => 'facility-grid', 'props' => ['category' => 'wellness', 'limit' => 10], 'settings' => []],
        ], $this->hotelA);

        $names = array_column($resolved[0]['data']['facilities'], 'name');

        $this->assertContains('Hotel A Spa', $names);
        $this->assertNotContains('Hotel B Spa', $names);
    }

    /** @test */
    public function facility_grid_respects_category_featured_and_limit_filters(): void
    {
        app()->make(CurrentHotel::class)->set($this->hotelA);

        Facility::create(['hotel_id' => $this->hotelA->id, 'name' => 'Spa One', 'slug' => 'spa-one', 'category' => 'wellness', 'featured' => true, 'status' => 'published']);
        Facility::create(['hotel_id' => $this->hotelA->id, 'name' => 'Spa Two', 'slug' => 'spa-two', 'category' => 'wellness', 'featured' => false, 'status' => 'published']);
        Facility::create(['hotel_id' => $this->hotelA->id, 'name' => 'Gym One', 'slug' => 'gym-one', 'category' => 'fitness', 'featured' => true, 'status' => 'published']);

        $service = app(PageRenderService::class);

        $resolved = $service->resolveSections([
            ['id' => 's1', 'type' => 'facility-grid', 'props' => ['category' => 'wellness', 'featured_only' => true, 'limit' => 10], 'settings' => []],
        ], $this->hotelA);

        $names = array_column($resolved[0]['data']['facilities'], 'name');

        $this->assertEquals(['Spa One'], $names);
    }

    /** @test */
    public function unpublished_facilities_never_appear_in_resolved_data(): void
    {
        app()->make(CurrentHotel::class)->set($this->hotelA);

        Facility::create(['hotel_id' => $this->hotelA->id, 'name' => 'Draft Spa', 'slug' => 'draft-spa', 'category' => 'wellness', 'status' => 'draft']);

        $resolved = app(PageRenderService::class)->resolveSections([
            ['id' => 's1', 'type' => 'facility-grid', 'props' => ['limit' => 10], 'settings' => []],
        ], $this->hotelA);

        $this->assertEmpty($resolved[0]['data']['facilities']);
    }

    /**
     * Design amendment #2's test: changing dynamic props must produce
     * genuinely fresh data on each call, not a cached/stale result from a
     * previous resolution - proving resolveOne() is safe to call
     * repeatedly as the admin edits.
     *
     * @test
     */
    public function live_preview_resolution_reflects_prop_changes_immediately(): void
    {
        app()->make(CurrentHotel::class)->set($this->hotelA);

        Facility::create(['hotel_id' => $this->hotelA->id, 'name' => 'Wellness Spa', 'slug' => 'wellness-spa', 'category' => 'wellness', 'status' => 'published']);
        Facility::create(['hotel_id' => $this->hotelA->id, 'name' => 'Fitness Gym', 'slug' => 'fitness-gym', 'category' => 'fitness', 'status' => 'published']);

        $service = app(PageRenderService::class);

        $wellnessResult = $service->resolveOne(
            ['id' => 's1', 'type' => 'facility-grid', 'props' => ['category' => 'wellness', 'limit' => 10], 'settings' => []],
            $this->hotelA
        );
        $this->assertEquals(['Wellness Spa'], array_column($wellnessResult['data']['facilities'], 'name'));

        // Same section id, DIFFERENT category prop - simulating the admin
        // changing the dropdown in the Settings Panel before saving.
        $fitnessResult = $service->resolveOne(
            ['id' => 's1', 'type' => 'facility-grid', 'props' => ['category' => 'fitness', 'limit' => 10], 'settings' => []],
            $this->hotelA
        );
        $this->assertEquals(['Fitness Gym'], array_column($fitnessResult['data']['facilities'], 'name'));

        // Prove it isn't accidentally memoized anywhere by calling the
        // original category again and confirming it's still correct.
        $wellnessAgain = $service->resolveOne(
            ['id' => 's1', 'type' => 'facility-grid', 'props' => ['category' => 'wellness', 'limit' => 10], 'settings' => []],
            $this->hotelA
        );
        $this->assertEquals(['Wellness Spa'], array_column($wellnessAgain['data']['facilities'], 'name'));
    }

    /** @test */
    public function the_resolve_preview_http_endpoint_returns_fresh_data_for_unsaved_props(): void
    {
        $admin = User::create([
            'hotel_id' => $this->hotelA->id, 'role' => 'hotel_admin', 'status' => 'active',
            'name' => 'Admin', 'email' => 'admin-' . uniqid() . '@example.com', 'password' => Hash::make('password'),
        ]);

        Facility::create(['hotel_id' => $this->hotelA->id, 'name' => 'Wellness Spa', 'slug' => 'wellness-spa', 'category' => 'wellness', 'status' => 'published']);

        $page = app(CreatePageAction::class)->execute($this->hotelA, ['name' => 'Home', 'slug' => '', 'is_home' => true]);

        $response = $this->actingAs($admin)->postJson("/admin/pages/{$page->id}/resolve-preview", [
            'sections' => [
                ['id' => 's1', 'type' => 'facility-grid', 'props' => ['category' => 'wellness', 'limit' => 10], 'settings' => []],
            ],
        ]);

        $response->assertOk();
        $response->assertJsonPath('section.data.facilities.0.name', 'Wellness Spa');
    }

    /** @test */
    public function unknown_section_type_is_rejected_at_draft_save_not_silently_accepted(): void
    {
        $admin = User::create([
            'hotel_id' => $this->hotelA->id, 'role' => 'hotel_admin', 'status' => 'active',
            'name' => 'Admin', 'email' => 'admin2-' . uniqid() . '@example.com', 'password' => Hash::make('password'),
        ]);

        $page = app(CreatePageAction::class)->execute($this->hotelA, ['name' => 'Home', 'slug' => '', 'is_home' => true]);

        $response = $this->actingAs($admin)->putJson("/admin/pages/{$page->id}/draft", [
            'sections' => [
                ['id' => 's1', 'type' => 'evil-custom-html', 'props' => ['html' => '<script>alert(1)</script>'], 'settings' => []],
            ],
        ]);

        $response->assertStatus(422);
    }
}
