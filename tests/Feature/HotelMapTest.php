<?php

namespace Tests\Feature;

use App\Models\Facility;
use App\Models\Hotel;
use App\Models\HotelMap;
use App\Models\User;
use App\Services\Map\MapDataValidator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * WRITTEN BUT NOT EXECUTED: Laravel/Composer are not installable in the sandbox this was
 * authored in. (MapDataValidator itself WAS executed there with plain php: 14 checks.)
 * Run with `php artisan test --filter=HotelMapTest`.
 */
class HotelMapTest extends TestCase
{
    use RefreshDatabase;

    protected Hotel $hotelA;
    protected Hotel $hotelB;
    protected User $adminA;
    protected array $demo;

    protected function setUp(): void
    {
        parent::setUp();
        $this->hotelA = Hotel::create(['name' => 'Hotel A', 'slug' => 'hotel-a-' . uniqid(), 'status' => 'active']);
        $this->hotelB = Hotel::create(['name' => 'Hotel B', 'slug' => 'hotel-b-' . uniqid(), 'status' => 'active']);
        $this->adminA = User::create([
            'hotel_id' => $this->hotelA->id, 'role' => 'hotel_admin', 'status' => 'active',
            'name' => 'Admin A', 'email' => 'a-' . uniqid() . '@example.com', 'password' => Hash::make('password'),
        ]);
        $this->demo = json_decode(file_get_contents(base_path('database/data/demo-hotel-map.json')), true);
    }

    /** @test */
    public function the_demo_map_file_passes_validation_with_no_warnings(): void
    {
        $r = (new MapDataValidator())->validate($this->demo);
        $this->assertSame([], $r['errors']);
        $this->assertSame([], $r['warnings']);
    }

    /** @test */
    public function admin_can_load_the_demo_map_and_it_belongs_to_their_hotel_only(): void
    {
        $this->actingAs($this->adminA)->post('/admin/map/demo')->assertRedirect('/admin/map');

        $this->assertSame(1, HotelMap::withoutGlobalScopes()->where('hotel_id', $this->hotelA->id)->count());
        $this->assertSame(0, HotelMap::withoutGlobalScopes()->where('hotel_id', $this->hotelB->id)->count());
    }

    /** @test */
    public function saving_valid_json_replaces_the_existing_map_instead_of_adding_a_second(): void
    {
        HotelMap::create(['hotel_id' => $this->hotelA->id, 'data' => ['old' => true]]);
        $this->demo['locations'][0]['name'] = 'Renamed Place';

        $this->actingAs($this->adminA)->put('/admin/map', ['json' => json_encode($this->demo)])->assertRedirect('/admin/map');

        $rows = HotelMap::withoutGlobalScopes()->where('hotel_id', $this->hotelA->id)->get();
        $this->assertCount(1, $rows);
        $this->assertSame('Renamed Place', $rows->first()->data['locations'][0]['name']);
    }

    /** @test */
    public function invalid_json_or_a_broken_map_is_rejected_and_the_stored_map_is_untouched(): void
    {
        HotelMap::create(['hotel_id' => $this->hotelA->id, 'data' => $this->demo]);

        $this->actingAs($this->adminA)->put('/admin/map', ['json' => '{not json'])->assertRedirect('/admin/map')->assertSessionHas('map_errors');

        $broken = $this->demo;
        $broken['locations'][0]['floor'] = 'no-such-floor';
        $this->actingAs($this->adminA)->put('/admin/map', ['json' => json_encode($broken)])->assertSessionHas('map_errors');

        $this->assertSame($this->demo['locations'][0]['floor'], HotelMap::withoutGlobalScopes()->where('hotel_id', $this->hotelA->id)->first()->data['locations'][0]['floor']);
    }

    /** @test */
    public function guests_never_see_another_hotels_map(): void
    {
        HotelMap::create(['hotel_id' => $this->hotelB->id, 'data' => $this->demo]);

        // With hotel A current, hotel B's map is invisible (tenant global scope).
        app(\App\Services\Tenancy\CurrentHotel::class)->set($this->hotelA);
        $this->assertNull(HotelMap::query()->first());
    }

    /** @test */
    public function the_guest_map_page_gets_null_when_no_map_exists_and_the_map_when_it_does(): void
    {
        app(\App\Services\Tenancy\CurrentHotel::class)->set($this->hotelA);

        $this->get('/map')->assertInertia(fn ($page) => $page->component('Guest/Map')->where('map', null));

        HotelMap::create(['hotel_id' => $this->hotelA->id, 'data' => $this->demo]);
        $this->get('/map')->assertInertia(fn ($page) => $page->component('Guest/Map')->has('map.floors', 5)->has('map.locations', 60));
    }

    /** @test */
    public function locations_with_a_ref_get_content_from_published_facilities_only(): void
    {
        app(\App\Services\Tenancy\CurrentHotel::class)->set($this->hotelA);
        Facility::create(['hotel_id' => $this->hotelA->id, 'name' => 'Serenity Spa', 'slug' => 'serenity-spa', 'short_description' => 'Real spa text', 'status' => 'published']);
        Facility::create(['hotel_id' => $this->hotelA->id, 'name' => 'Fitness Center', 'slug' => 'fitness-center', 'short_description' => 'Draft text', 'status' => 'draft']);
        HotelMap::create(['hotel_id' => $this->hotelA->id, 'data' => $this->demo]);

        $this->get('/map')->assertInertia(function ($page) {
            $locs = collect($page->toArray()['props']['map']['locations'])->keyBy('id');
            $this->assertSame('/facilities/serenity-spa', $locs['spa']['details_url']);
            $this->assertNull($locs['gym']['details_url'] ?? null);            // draft content is never linked
            $this->assertSame('Massages, facials and treatments in a calm, candle-lit setting.', $locs['spa']['description']); // map's own text wins
        });
    }

    /** @test */
    public function only_signed_in_staff_can_reach_the_map_admin(): void
    {
        $this->get('/admin/map')->assertRedirect();
        $this->put('/admin/map', ['json' => '{}'])->assertRedirect();
    }

    /* ---------------- visual builder endpoints ---------------- */

    /** @test */
    public function the_builder_page_gets_the_map_and_this_hotels_content_but_never_another_hotels(): void
    {
        app(\App\Services\Tenancy\CurrentHotel::class)->set($this->hotelA);
        Facility::create(['hotel_id' => $this->hotelA->id, 'name' => 'Serenity Spa', 'slug' => 'serenity-spa', 'status' => 'published']);
        Facility::create(['hotel_id' => $this->hotelA->id, 'name' => 'Secret Draft', 'slug' => 'secret-draft', 'status' => 'draft']);
        Facility::create(['hotel_id' => $this->hotelB->id, 'name' => 'Other Hotel Pool', 'slug' => 'other-pool', 'status' => 'published']);
        HotelMap::create(['hotel_id' => $this->hotelA->id, 'data' => $this->demo]);

        $this->actingAs($this->adminA)->get('/admin/map/builder')->assertInertia(function ($page) {
            $page->component('Admin/Map/Builder')->has('map.floors', 5);
            $slugs = collect($page->toArray()['props']['content'])->pluck('slug')->sort()->values()->all();
            $this->assertSame(['secret-draft', 'serenity-spa'], $slugs); // this hotel's (draft flagged); never 'other-pool'
        });
    }

    /** @test */
    public function the_builder_starts_empty_for_a_hotel_with_no_map(): void
    {
        $this->actingAs($this->adminA)->get('/admin/map/builder')
            ->assertInertia(fn ($page) => $page->component('Admin/Map/Builder')->where('map', null));
    }

    /** @test */
    public function saving_from_the_builder_stores_the_map_and_returns_to_the_builder(): void
    {
        $this->demo['locations'][0]['name'] = 'Edited In Builder';

        $this->actingAs($this->adminA)->put('/admin/map/save', ['data' => $this->demo])
            ->assertRedirect('/admin/map/builder')->assertSessionHas('map_saved');

        $this->assertSame('Edited In Builder', HotelMap::withoutGlobalScopes()->where('hotel_id', $this->hotelA->id)->first()->data['locations'][0]['name']);
    }

    /** @test */
    public function the_builder_save_survives_laravels_empty_string_to_null_middleware(): void
    {
        // The builder sends null for blank optional fields, but a browser/proxy may send "".
        $this->demo['locations'][0]['description'] = '';
        $this->demo['floors'][0]['plan_image'] = '';

        $this->actingAs($this->adminA)->put('/admin/map/save', ['data' => $this->demo])->assertSessionHas('map_saved');
    }

    /** @test */
    public function a_broken_builder_save_is_refused_with_every_problem_listed_and_the_old_map_kept(): void
    {
        HotelMap::create(['hotel_id' => $this->hotelA->id, 'data' => $this->demo]);
        $broken = $this->demo;
        $broken['locations'][0]['floor'] = 'nope';
        $broken['locations'][1]['category'] = 'spaceship';

        $this->actingAs($this->adminA)->put('/admin/map/save', ['data' => $broken])
            ->assertRedirect('/admin/map/builder')
            ->assertSessionHas('map_errors', fn ($errors) => count($errors) >= 2);

        $this->assertSame($this->demo['locations'][0]['floor'], HotelMap::withoutGlobalScopes()->where('hotel_id', $this->hotelA->id)->first()->data['locations'][0]['floor']);
    }

    /** @test */
    public function builder_endpoints_require_sign_in(): void
    {
        $this->get('/admin/map/builder')->assertRedirect();
        $this->put('/admin/map/save', ['data' => $this->demo])->assertRedirect();
    }

    /* ---------------- "View Details" links ---------------- */

    /** @test */
    public function a_place_can_link_to_a_published_page_builder_page_but_never_a_draft(): void
    {
        app(\App\Services\Tenancy\CurrentHotel::class)->set($this->hotelA);
        // A guest-visible page = status "published" AND a published version (see Page::scopePublished).
        $spaMenu = \App\Models\Page::create(['hotel_id' => $this->hotelA->id, 'name' => 'Spa Menu', 'slug' => 'spa-menu', 'status' => 'draft']);
        $version = \App\Models\PageVersion::create(['page_id' => $spaMenu->id, 'sections' => [], 'state' => 'published', 'published_at' => now()]);
        $spaMenu->update(['status' => 'published', 'published_version_id' => $version->id]);
        \App\Models\Page::create(['hotel_id' => $this->hotelA->id, 'name' => 'Draft', 'slug' => 'draft-page', 'status' => 'draft']);

        $map = $this->demo;
        $map['locations'][0]['ref'] = ['type' => 'page', 'slug' => 'spa-menu'];
        $map['locations'][1]['ref'] = ['type' => 'page', 'slug' => 'draft-page'];
        HotelMap::create(['hotel_id' => $this->hotelA->id, 'data' => $map]);

        $this->get('/map')->assertInertia(function ($page) {
            $locs = $page->toArray()['props']['map']['locations'];
            $this->assertSame('/pages/spa-menu', $locs[0]['details_url']);
            $this->assertArrayNotHasKey('details_url', $locs[1]); // a draft page has no guest URL, so no button to a 404
        });
    }

    /** @test */
    public function an_address_typed_on_the_place_wins_over_the_linked_content(): void
    {
        app(\App\Services\Tenancy\CurrentHotel::class)->set($this->hotelA);
        Facility::create(['hotel_id' => $this->hotelA->id, 'name' => 'Serenity Spa', 'slug' => 'serenity-spa', 'status' => 'published']);
        $map = $this->demo;
        $spa = array_search('spa', array_column($map['locations'], 'id'));
        $map['locations'][$spa]['link'] = '/pages/spa-offers';
        HotelMap::create(['hotel_id' => $this->hotelA->id, 'data' => $map]);

        $this->get('/map')->assertInertia(fn ($page) => $this->assertSame('/pages/spa-offers', collect($page->toArray()['props']['map']['locations'])->firstWhere('id', 'spa')['details_url']));
    }

    /** @test */
    public function a_link_on_a_place_with_no_ref_still_works(): void
    {
        app(\App\Services\Tenancy\CurrentHotel::class)->set($this->hotelA);
        $map = $this->demo;
        $map['locations'][2]['link'] = 'https://example.com/menu';
        HotelMap::create(['hotel_id' => $this->hotelA->id, 'data' => $map]);

        $this->get('/map')->assertInertia(fn ($page) => $this->assertSame('https://example.com/menu', $page->toArray()['props']['map']['locations'][2]['details_url']));
    }

    /** @test */
    public function unsafe_link_addresses_are_refused_when_saving(): void
    {
        foreach (['javascript:alert(1)', '//evil.example.com', 'pages/x'] as $bad) {
            $map = $this->demo;
            $map['locations'][0]['link'] = $bad;
            $this->actingAs($this->adminA)->put('/admin/map/save', ['data' => $map])->assertSessionHas('map_errors');
        }
    }

    /** @test */
    public function map_place_query_is_passed_through_so_links_can_open_the_map_on_a_place(): void
    {
        app(\App\Services\Tenancy\CurrentHotel::class)->set($this->hotelA);
        HotelMap::create(['hotel_id' => $this->hotelA->id, 'data' => $this->demo]);

        $this->get('/map?place=spa')->assertInertia(fn ($page) => $page->where('place', 'spa'));
        $this->get('/map')->assertInertia(fn ($page) => $page->where('place', null));
    }

    /** @test */
    public function the_builders_link_choices_include_pages_and_flag_unpublished_content(): void
    {
        Facility::create(['hotel_id' => $this->hotelA->id, 'name' => 'Live Spa', 'slug' => 'live-spa', 'status' => 'published']);
        Facility::create(['hotel_id' => $this->hotelA->id, 'name' => 'Draft Pool', 'slug' => 'draft-pool', 'status' => 'draft']);
        \App\Models\Page::create(['hotel_id' => $this->hotelA->id, 'name' => 'Offers', 'slug' => 'offers', 'status' => 'draft']);

        $this->actingAs($this->adminA)->get('/admin/map/builder')->assertInertia(function ($page) {
            $c = collect($page->toArray()['props']['content'])->keyBy('slug');
            $this->assertTrue($c['live-spa']['published']);
            $this->assertFalse($c['draft-pool']['published']);   // listed, but flagged so the admin is warned
            $this->assertSame('page', $c['offers']['type']);
            $this->assertFalse($c['offers']['published']);
        });
    }
}
