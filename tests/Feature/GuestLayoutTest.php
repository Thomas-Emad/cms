<?php

namespace Tests\Feature;

use App\Models\Hotel;
use App\Models\HotelSettings;
use App\Models\User;
use App\Services\Layout\GuestLayoutConfig;
use App\Services\Layout\GuestLayoutStore;
use App\Services\Tenancy\CurrentHotel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * WRITTEN BUT NOT EXECUTED: Laravel/Composer are not installable in the sandbox this was authored
 * in. (GuestLayoutConfig itself WAS executed there with plain php: 22 checks, all passing.)
 * Run with `php artisan test --filter=GuestLayoutTest`.
 */
class GuestLayoutTest extends TestCase
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
            'name' => 'Admin A', 'email' => 'a-'.uniqid().'@example.com', 'password' => Hash::make('password'),
        ]);
    }

    public function test_a_hotel_with_no_saved_layout_gets_the_default_classic_config(): void
    {
        $config = app(GuestLayoutStore::class)->forHotel($this->hotelA->id);
        $this->assertSame('classic', $config['template']);
        $this->assertSame(GuestLayoutConfig::defaults(), $config);
    }

    public function test_saving_a_layout_is_isolated_per_hotel_and_does_not_touch_other_settings(): void
    {
        HotelSettings::create(['hotel_id' => $this->hotelA->id, 'metadata' => ['some_other_setting' => 'keep-me']]);

        app(GuestLayoutStore::class)->save($this->hotelA->id, array_merge(GuestLayoutConfig::defaults(), ['template' => 'tv']));

        $meta = HotelSettings::where('hotel_id', $this->hotelA->id)->value('metadata');
        $this->assertSame('tv', $meta['guest_layout']['template']);
        $this->assertSame('keep-me', $meta['some_other_setting']); // untouched

        $this->assertSame('classic', app(GuestLayoutStore::class)->forHotel($this->hotelB->id)['template']); // hotel B unaffected
    }

    public function test_the_admin_can_save_a_valid_layout_and_guests_get_it_via_the_shared_inertia_prop(): void
    {
        app(CurrentHotel::class)->set($this->hotelA);
        $config = array_merge(GuestLayoutConfig::defaults(), ['template' => 'tv', 'headline' => 'Welcome']);

        $this->actingAs($this->adminA)->put('/admin/layout', $config)
            ->assertRedirect('/admin/layout')->assertSessionHas('layout_saved');

        $this->get('/facilities')->assertInertia(fn ($page) => $page->where('guestLayout.template', 'tv')->where('guestLayout.headline', 'Welcome'));
    }

    public function test_an_invalid_layout_is_refused_with_every_problem_listed_and_nothing_is_saved(): void
    {
        $this->actingAs($this->adminA)->put('/admin/layout', ['template' => 'not-real', 'items' => []])
            ->assertRedirect('/admin/layout')
            ->assertSessionHas('layout_errors', fn ($errors) => count($errors) >= 2);

        $this->assertNull(HotelSettings::where('hotel_id', $this->hotelA->id)->value('metadata'));
    }

    public function test_the_admin_area_never_gets_the_guest_layout_prop_even_when_signed_in(): void
    {
        $this->actingAs($this->adminA)->get('/admin/map')->assertInertia(fn ($page) => $page->where('guestLayout', null));
    }

    public function test_only_signed_in_staff_can_change_the_layout(): void
    {
        $this->get('/admin/layout')->assertRedirect();
        $this->put('/admin/layout', GuestLayoutConfig::defaults())->assertRedirect();
    }
}
