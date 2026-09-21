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
}
