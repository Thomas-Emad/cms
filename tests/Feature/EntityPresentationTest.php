<?php

namespace Tests\Feature;

use App\Actions\Pages\CreatePageAction;
use App\Actions\Pages\PublishPageAction;
use App\Actions\Presentations\CreatePresentationAction;
use App\Actions\Presentations\PublishPresentationAction;
use App\Models\EntityPresentation;
use App\Models\Hotel;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * As with every previous checkpoint: not executed end-to-end here (no
 * Composer/Packagist access in this sandbox to install Laravel). See
 * verification/README.md for what was independently verified against
 * real MySQL instead - verify_entity_presentation.php covers the same
 * scenarios at the data layer.
 */
class EntityPresentationTest extends TestCase
{
    use RefreshDatabase;

    protected Hotel $hotelA;

    protected Hotel $hotelB;

    protected User $adminA;

    protected Restaurant $restaurantA;

    protected function setUp(): void
    {
        parent::setUp();

        $this->hotelA = Hotel::create(['name' => 'Hotel A', 'slug' => 'hotel-a-'.uniqid(), 'status' => 'active']);
        $this->hotelB = Hotel::create(['name' => 'Hotel B', 'slug' => 'hotel-b-'.uniqid(), 'status' => 'active']);

        $this->adminA = User::create([
            'hotel_id' => $this->hotelA->id, 'role' => 'hotel_admin', 'status' => 'active',
            'name' => 'Admin A', 'email' => 'admin-a-'.uniqid().'@example.com', 'password' => Hash::make('password'),
        ]);

        $this->restaurantA = Restaurant::create(['hotel_id' => $this->hotelA->id, 'name' => 'Azure', 'slug' => 'azure', 'status' => 'published']);
    }

    /** @test */
    public function opening_the_restaurant_builder_creates_a_presentation_if_none_exists(): void
    {
        $response = $this->actingAs($this->adminA)->get("/admin/restaurants/{$this->restaurantA->id}/presentation/builder");

        $response->assertOk();
        $response->assertInertia(fn ($assert) => $assert
            ->component('Admin/Pages/Builder')
            ->where('context.type', 'restaurant')
            ->where('context.entityName', 'Azure')
        );
    }

    /** @test */
    public function opening_the_builder_twice_reuses_the_same_presentation_not_a_duplicate(): void
    {
        $this->actingAs($this->adminA)->get("/admin/restaurants/{$this->restaurantA->id}/presentation/builder");
        $firstId = EntityPresentation::first()->id;

        $this->actingAs($this->adminA)->get("/admin/restaurants/{$this->restaurantA->id}/presentation/builder");

        $this->assertSame(1, EntityPresentation::count());
        $this->assertSame($firstId, EntityPresentation::first()->id);
    }

    /** @test */
    public function a_hotel_admin_cannot_open_another_hotels_restaurant_presentation(): void
    {
        $restaurantB = Restaurant::create(['hotel_id' => $this->hotelB->id, 'name' => 'Other', 'slug' => 'other', 'status' => 'published']);

        $response = $this->actingAs($this->adminA)->get("/admin/restaurants/{$restaurantB->id}/presentation/builder");

        $response->assertForbidden();
    }

    /** @test */
    public function restaurant_draft_saves_correctly_through_the_builder_endpoint(): void
    {
        $this->actingAs($this->adminA)->get("/admin/restaurants/{$this->restaurantA->id}/presentation/builder");

        $response = $this->actingAs($this->adminA)->putJson(
            "/admin/restaurants/{$this->restaurantA->id}/presentation/draft",
            ['sections' => [['id' => 'h1', 'type' => 'restaurant-hero', 'props' => [], 'settings' => []]]]
        );

        $response->assertRedirect();

        $presentation = EntityPresentation::first();
        $this->assertSame('restaurant-hero', $presentation->draftVersion->sections['sections'][0]['type']);
    }

    /** @test */
    public function restaurant_preview_resolves_the_current_restaurants_actual_data(): void
    {
        $this->restaurantA->update(['cuisine' => 'Mediterranean']);

        app(CreatePresentationAction::class)->firstOrCreate($this->hotelA, $this->restaurantA, [
            ['id' => 'h1', 'type' => 'restaurant-hero', 'props' => [], 'settings' => []],
        ]);

        $response = $this->actingAs($this->adminA)->get("/admin/restaurants/{$this->restaurantA->id}/presentation/preview");

        $response->assertInertia(fn ($assert) => $assert
            ->where('sections.0.data.restaurant.cuisine', 'Mediterranean')
        );
    }

    /** @test */
    public function restaurant_publish_works_and_guest_page_then_shows_the_custom_presentation(): void
    {
        app(CreatePresentationAction::class)->firstOrCreate($this->hotelA, $this->restaurantA, [
            ['id' => 'h1', 'type' => 'restaurant-hero', 'props' => [], 'settings' => []],
        ]);

        $publishResponse = $this->actingAs($this->adminA)->post("/admin/restaurants/{$this->restaurantA->id}/presentation/publish");
        $publishResponse->assertRedirect(route('admin.restaurants.index'));

        $guestResponse = $this->get("/restaurants/{$this->restaurantA->slug}");
        $guestResponse->assertInertia(fn ($assert) => $assert->component('Guest/Restaurants/PresentationShow'));
    }

    /** @test */
    public function a_restaurant_without_a_published_presentation_still_falls_back_to_the_original_guest_page(): void
    {
        $response = $this->get("/restaurants/{$this->restaurantA->slug}");

        $response->assertInertia(fn ($assert) => $assert->component('Guest/Restaurants/Show'));
    }

    /** @test */
    public function menu_changes_after_publish_are_reflected_without_republishing(): void
    {
        $presentation = app(CreatePresentationAction::class)->firstOrCreate($this->hotelA, $this->restaurantA, [
            ['id' => 'm1', 'type' => 'restaurant-menu', 'props' => [], 'settings' => []],
        ]);
        app(PublishPresentationAction::class)->execute($presentation);

        $menu = $this->restaurantA->menus()->create(['name' => 'Main', 'is_active' => true]);
        $category = $menu->categories()->create(['name' => 'Starters', 'sort_order' => 0]);
        $category->items()->create(['name' => 'Soup', 'price' => 8.00, 'is_available' => true, 'sort_order' => 0]);

        // No re-publish happened - the menu was added AFTER publish.
        $guestResponse = $this->get("/restaurants/{$this->restaurantA->slug}");

        $guestResponse->assertInertia(fn ($assert) => $assert
            ->where('sections.0.data.menu.categories.0.items.0.name', 'Soup')
        );
    }

    /** @test */
    public function existing_standalone_page_lifecycle_is_completely_unaffected(): void
    {
        $page = app(CreatePageAction::class)->execute($this->hotelA, [
            'name' => 'Homepage', 'slug' => '', 'is_home' => true,
        ], [['id' => 'h1', 'type' => 'hero', 'props' => ['title' => 'Still works'], 'settings' => []]]);

        app(PublishPageAction::class)->execute($page->fresh());

        $response = $this->get('/');
        $response->assertInertia(fn ($assert) => $assert->where('sections.0.props.title', 'Still works'));
    }
}
