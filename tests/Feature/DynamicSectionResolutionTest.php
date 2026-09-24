<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Experience;
use App\Models\Hotel;
use App\Models\Offer;
use App\Models\Restaurant;
use App\Models\Service;
use App\Services\PageBuilder\PageRenderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DynamicSectionResolutionTest extends TestCase
{
    use RefreshDatabase;

    protected Hotel $hotelA;

    protected Hotel $hotelB;

    protected function setUp(): void
    {
        parent::setUp();

        $this->hotelA = Hotel::create(['name' => 'Hotel A', 'slug' => 'hotel-a-'.uniqid(), 'status' => 'active']);
        $this->hotelB = Hotel::create(['name' => 'Hotel B', 'slug' => 'hotel-b-'.uniqid(), 'status' => 'active']);
    }

    protected function resolve(string $type, array $props, Hotel $hotel): array
    {
        return app(PageRenderService::class)->resolveOne(
            ['id' => 's1', 'type' => $type, 'props' => $props, 'settings' => []],
            $hotel
        );
    }

    /** @test */
    public function restaurant_grid_resolves_only_the_current_hotel_and_respects_cuisine_and_featured_filters(): void
    {
        Restaurant::create(['hotel_id' => $this->hotelA->id, 'name' => 'Azure', 'slug' => 'azure', 'cuisine' => 'Mediterranean', 'featured' => true, 'status' => 'published']);
        Restaurant::create(['hotel_id' => $this->hotelA->id, 'name' => 'Sky Lounge', 'slug' => 'sky-lounge', 'cuisine' => 'Cocktails', 'featured' => false, 'status' => 'published']);
        Restaurant::create(['hotel_id' => $this->hotelB->id, 'name' => 'Other Hotel Restaurant', 'slug' => 'other', 'cuisine' => 'Mediterranean', 'featured' => true, 'status' => 'published']);

        $result = $this->resolve('restaurant-grid', ['cuisine' => 'Mediterranean', 'limit' => 10], $this->hotelA);
        $names = array_column($result['data']['restaurants'], 'name');

        $this->assertEquals(['Azure'], $names);
    }

    /** @test */
    public function service_grid_resolves_only_the_current_hotels_published_services(): void
    {
        Service::create(['hotel_id' => $this->hotelA->id, 'name' => 'Room Service', 'slug' => 'room-service', 'status' => 'published']);
        Service::create(['hotel_id' => $this->hotelA->id, 'name' => 'Draft Service', 'slug' => 'draft-service', 'status' => 'draft']);
        Service::create(['hotel_id' => $this->hotelB->id, 'name' => 'Other Hotel Service', 'slug' => 'other-service', 'status' => 'published']);

        $result = $this->resolve('service-grid', ['limit' => 10], $this->hotelA);
        $names = array_column($result['data']['services'], 'name');

        $this->assertEquals(['Room Service'], $names);
    }

    /** @test */
    public function events_upcoming_only_excludes_past_events_and_respects_tenant(): void
    {
        Event::create(['hotel_id' => $this->hotelA->id, 'title' => 'Future Event', 'slug' => 'future', 'start_date' => now()->addDays(3), 'status' => 'published']);
        Event::create(['hotel_id' => $this->hotelA->id, 'title' => 'Past Event', 'slug' => 'past', 'start_date' => now()->subDays(3), 'status' => 'published']);
        Event::create(['hotel_id' => $this->hotelB->id, 'title' => 'Other Hotel Event', 'slug' => 'other', 'start_date' => now()->addDays(3), 'status' => 'published']);

        $result = $this->resolve('events', ['limit' => 10, 'upcoming_only' => true], $this->hotelA);
        $titles = array_column($result['data']['events'], 'title');

        $this->assertEquals(['Future Event'], $titles);
    }

    /** @test */
    public function events_with_upcoming_only_false_includes_past_events_for_the_current_tenant_only(): void
    {
        Event::create(['hotel_id' => $this->hotelA->id, 'title' => 'Future Event', 'slug' => 'future2', 'start_date' => now()->addDays(3), 'status' => 'published']);
        Event::create(['hotel_id' => $this->hotelA->id, 'title' => 'Past Event', 'slug' => 'past2', 'start_date' => now()->subDays(3), 'status' => 'published']);

        $result = $this->resolve('events', ['limit' => 10, 'upcoming_only' => false], $this->hotelA);
        $titles = array_column($result['data']['events'], 'title');

        $this->assertCount(2, $titles);
        $this->assertContains('Past Event', $titles);
    }

    /** @test */
    public function offers_active_only_excludes_expired_offers_and_respects_tenant(): void
    {
        Offer::create(['hotel_id' => $this->hotelA->id, 'title' => 'Active Offer', 'slug' => 'active', 'status' => 'published', 'valid_until' => now()->addDays(10)]);
        Offer::create(['hotel_id' => $this->hotelA->id, 'title' => 'Expired Offer', 'slug' => 'expired', 'status' => 'published', 'valid_until' => now()->subDays(1)]);
        Offer::create(['hotel_id' => $this->hotelB->id, 'title' => 'Other Hotel Offer', 'slug' => 'other', 'status' => 'published', 'valid_until' => now()->addDays(10)]);

        $result = $this->resolve('offers', ['limit' => 10, 'active_only' => true], $this->hotelA);
        $titles = array_column($result['data']['offers'], 'title');

        $this->assertEquals(['Active Offer'], $titles);
    }

    /** @test */
    public function offers_featured_only_filters_correctly_on_top_of_active_only(): void
    {
        Offer::create(['hotel_id' => $this->hotelA->id, 'title' => 'Featured Active', 'slug' => 'fa', 'status' => 'published', 'featured' => true, 'valid_until' => now()->addDays(10)]);
        Offer::create(['hotel_id' => $this->hotelA->id, 'title' => 'Unfeatured Active', 'slug' => 'ua', 'status' => 'published', 'featured' => false, 'valid_until' => now()->addDays(10)]);

        $result = $this->resolve('offers', ['limit' => 10, 'active_only' => true, 'featured_only' => true], $this->hotelA);
        $titles = array_column($result['data']['offers'], 'title');

        $this->assertEquals(['Featured Active'], $titles);
    }

    /** @test */
    public function experiences_resolves_only_the_current_hotel_and_respects_category_and_featured_filters(): void
    {
        Experience::create(['hotel_id' => $this->hotelA->id, 'title' => 'Yoga', 'slug' => 'yoga', 'category' => 'wellness', 'featured' => true, 'status' => 'published']);
        Experience::create(['hotel_id' => $this->hotelA->id, 'title' => 'Cooking Class', 'slug' => 'cooking', 'category' => 'culinary', 'featured' => true, 'status' => 'published']);
        Experience::create(['hotel_id' => $this->hotelB->id, 'title' => 'Other Hotel Experience', 'slug' => 'other', 'category' => 'wellness', 'featured' => true, 'status' => 'published']);

        $result = $this->resolve('experiences', ['category' => 'wellness', 'featured_only' => true, 'limit' => 10], $this->hotelA);
        $titles = array_column($result['data']['experiences'], 'title');

        $this->assertEquals(['Yoga'], $titles);
    }
}
