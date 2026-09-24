<?php

namespace Tests\Feature;

use App\Actions\Pages\CreatePageAction;
use App\Models\Hotel;
use App\Models\Page;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * As with every prior checkpoint: not executed end-to-end here (no
 * Composer/Packagist access in this sandbox to install Laravel itself).
 * See verification/README.md for what was independently verified against
 * real MySQL instead.
 */
class PagesAdminUiTest extends TestCase
{
    use RefreshDatabase;

    protected Hotel $hotelA;

    protected Hotel $hotelB;

    protected User $adminA;

    protected function setUp(): void
    {
        parent::setUp();

        $this->hotelA = Hotel::create(['name' => 'Hotel A', 'slug' => 'hotel-a-'.uniqid(), 'status' => 'active']);
        $this->hotelB = Hotel::create(['name' => 'Hotel B', 'slug' => 'hotel-b-'.uniqid(), 'status' => 'active']);

        $this->adminA = User::create([
            'hotel_id' => $this->hotelA->id, 'role' => 'hotel_admin', 'status' => 'active',
            'name' => 'Admin A', 'email' => 'admin-a-'.uniqid().'@example.com', 'password' => Hash::make('password'),
        ]);
    }

    /** @test */
    public function pages_index_only_shows_the_current_hotels_pages(): void
    {
        app(CreatePageAction::class)->execute($this->hotelA, ['name' => 'My Page', 'slug' => 'my-page']);
        app(CreatePageAction::class)->execute($this->hotelB, ['name' => 'Other Hotel Page', 'slug' => 'other-page']);

        $response = $this->actingAs($this->adminA)->get('/admin/pages');

        $response->assertInertia(fn ($assert) => $assert
            ->component('Admin/Pages/Index')
            ->has('pages.data', 1)
            ->where('pages.data.0.name', 'My Page')
        );
    }

    /** @test */
    public function a_user_cannot_open_another_hotels_page_via_the_builder_edit_or_preview_routes(): void
    {
        $otherPage = app(CreatePageAction::class)->execute($this->hotelB, ['name' => 'Their Page', 'slug' => 'their-page']);

        $this->actingAs($this->adminA)->get("/admin/pages/{$otherPage->id}/builder")->assertForbidden();
        $this->actingAs($this->adminA)->get("/admin/pages/{$otherPage->id}/preview")->assertForbidden();
    }

    /** @test */
    public function creating_a_page_works_and_redirects_directly_to_its_builder(): void
    {
        $response = $this->actingAs($this->adminA)->post('/admin/pages', [
            'name' => 'About Us',
            'slug' => 'about-us',
            'is_home' => false,
        ]);

        $page = Page::where('hotel_id', $this->hotelA->id)->where('slug', 'about-us')->first();

        $this->assertNotNull($page, 'Page was not created.');
        $response->assertRedirect(route('admin.pages.builder', $page));
    }

    /** @test */
    public function a_newly_created_page_starts_as_draft_with_a_draft_version_already_attached(): void
    {
        $this->actingAs($this->adminA)->post('/admin/pages', [
            'name' => 'New Page', 'slug' => 'new-page', 'is_home' => false,
        ]);

        $page = Page::where('hotel_id', $this->hotelA->id)->where('slug', 'new-page')->first();

        $this->assertSame('draft', $page->status);
        $this->assertNotNull($page->draft_version_id);
        $this->assertNull($page->published_version_id);
        $this->assertNotNull($page->draftVersion, 'CreatePageAction should attach an initial draft version.');
    }

    /** @test */
    public function slug_uniqueness_is_enforced_per_hotel_not_globally(): void
    {
        app(CreatePageAction::class)->execute($this->hotelB, ['name' => 'Shared Slug Elsewhere', 'slug' => 'promo']);

        // Same slug, DIFFERENT hotel - must succeed, since uniqueness is
        // scoped by hotel_id, not global.
        $response = $this->actingAs($this->adminA)->post('/admin/pages', [
            'name' => 'My Promo', 'slug' => 'promo', 'is_home' => false,
        ]);

        $response->assertSessionDoesntHaveErrors('slug');
        $this->assertNotNull(Page::where('hotel_id', $this->hotelA->id)->where('slug', 'promo')->first());
    }

    /** @test */
    public function slug_must_be_unique_within_the_same_hotel(): void
    {
        app(CreatePageAction::class)->execute($this->hotelA, ['name' => 'First', 'slug' => 'duplicate-slug']);

        $response = $this->actingAs($this->adminA)->post('/admin/pages', [
            'name' => 'Second', 'slug' => 'duplicate-slug', 'is_home' => false,
        ]);

        $response->assertSessionHasErrors('slug');
    }

    /** @test */
    public function creating_a_home_page_ignores_any_typed_slug_and_normalizes_it_to_empty(): void
    {
        $this->actingAs($this->adminA)->post('/admin/pages', [
            'name' => 'Homepage', 'slug' => 'this-should-be-ignored', 'is_home' => true,
        ]);

        $page = Page::where('hotel_id', $this->hotelA->id)->where('is_home', true)->first();

        $this->assertNotNull($page);
        $this->assertSame('', $page->slug);
    }

    /** @test */
    public function a_non_home_page_requires_a_slug(): void
    {
        $response = $this->actingAs($this->adminA)->post('/admin/pages', [
            'name' => 'No Slug Page', 'slug' => '', 'is_home' => false,
        ]);

        $response->assertSessionHasErrors('slug');
    }

    /** @test */
    public function name_is_required(): void
    {
        $response = $this->actingAs($this->adminA)->post('/admin/pages', [
            'name' => '', 'slug' => 'x', 'is_home' => false,
        ]);

        $response->assertSessionHasErrors('name');
    }

    /** @test */
    public function search_filters_the_index_by_name_or_slug(): void
    {
        app(CreatePageAction::class)->execute($this->hotelA, ['name' => 'Spa Promotions', 'slug' => 'spa-promo']);
        app(CreatePageAction::class)->execute($this->hotelA, ['name' => 'Contact', 'slug' => 'contact']);

        $response = $this->actingAs($this->adminA)->get('/admin/pages?q=spa');

        $response->assertInertia(fn ($assert) => $assert
            ->has('pages.data', 1)
            ->where('pages.data.0.name', 'Spa Promotions')
        );
    }

    /** @test */
    public function hotel_staff_can_create_pages_but_only_hotel_admin_can_publish(): void
    {
        $staff = User::create([
            'hotel_id' => $this->hotelA->id, 'role' => 'hotel_staff', 'status' => 'active',
            'name' => 'Staff', 'email' => 'staff-'.uniqid().'@example.com', 'password' => Hash::make('password'),
        ]);

        $createResponse = $this->actingAs($staff)->post('/admin/pages', [
            'name' => 'Staff Page', 'slug' => 'staff-page', 'is_home' => false,
        ]);
        $createResponse->assertRedirect();

        $page = Page::where('hotel_id', $this->hotelA->id)->where('slug', 'staff-page')->first();

        $publishResponse = $this->actingAs($staff)->post("/admin/pages/{$page->id}/publish");
        $publishResponse->assertForbidden();
    }

    /** @test */
    public function the_existing_builder_draft_and_publish_flow_still_works_unchanged(): void
    {
        $createResponse = $this->actingAs($this->adminA)->post('/admin/pages', [
            'name' => 'Flow Test', 'slug' => 'flow-test', 'is_home' => false,
        ]);
        $page = Page::where('hotel_id', $this->hotelA->id)->where('slug', 'flow-test')->first();
        $createResponse->assertRedirect(route('admin.pages.builder', $page));

        $this->actingAs($this->adminA)->get("/admin/pages/{$page->id}/builder")->assertOk();

        $saveResponse = $this->actingAs($this->adminA)->putJson("/admin/pages/{$page->id}/draft", [
            'sections' => [['id' => 'hero-1', 'type' => 'hero', 'props' => ['title' => 'Hello'], 'settings' => []]],
        ]);
        $saveResponse->assertRedirect();

        $publishResponse = $this->actingAs($this->adminA)->post("/admin/pages/{$page->id}/publish");
        $publishResponse->assertRedirect(route('admin.pages.index'));

        $page->refresh();
        $this->assertSame('published', $page->status);
        $this->assertNotNull($page->published_version_id);
    }
}
