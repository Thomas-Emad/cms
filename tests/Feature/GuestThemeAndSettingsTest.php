<?php

namespace Tests\Feature;

use App\Models\Hotel;
use App\Models\HotelSettings;
use App\Models\Theme;
use App\Models\User;
use App\Services\Layout\GuestLayoutStore;
use App\Services\Tenancy\CurrentHotel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class GuestThemeAndSettingsTest extends TestCase
{
    use RefreshDatabase;

    protected Hotel $hotel;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->hotel = Hotel::create([
            'name' => 'Hilton Grand Horizon',
            'slug' => 'hilton-grand-horizon',
            'status' => 'active',
            'address' => '1 Horizon Bay Drive',
            'timezone' => 'UTC',
        ]);

        $this->admin = User::create([
            'hotel_id' => $this->hotel->id,
            'role' => 'hotel_admin',
            'status' => 'active',
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
        ]);

        app(CurrentHotel::class)->set($this->hotel);
    }

    public function test_admin_settings_page_is_accessible_and_does_not_404(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/settings');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Admin/Settings/Index')
            ->has('guestView')
        );
    }

    public function test_admin_can_update_guest_view_and_sync_layout_store(): void
    {
        $response = $this->actingAs($this->admin)->patch('/admin/settings/guest-view', [
            'guest_view' => 'tv',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Check hotel settings
        $settings = HotelSettings::where('hotel_id', $this->hotel->id)->first();
        $this->assertNotNull($settings);
        $this->assertSame('tv', $settings->guest_view);
        $this->assertSame('tv', $settings->metadata['guest_layout']['template']);

        // Check GuestLayoutStore returns tv
        $layoutConfig = app(GuestLayoutStore::class)->forHotel($this->hotel->id);
        $this->assertSame('tv', $layoutConfig['template']);
    }

    public function test_admin_theme_page_is_accessible_with_presets_and_color_options(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/theme');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Admin/Theme/Edit')
            ->has('theme')
            ->has('presets', 8)
            ->where('presets.0.id', 'emerald')
            ->where('presets.0.primary_color', '#059669')
            ->where('presets.0.header_bg', '#064e3b')
            ->where('presets.0.footer_bg', '#022c22')
        );
    }

    public function test_admin_can_update_guest_theme_to_green_style(): void
    {
        $response = $this->actingAs($this->admin)->put('/admin/theme', [
            'name' => 'Emerald Luxury Theme',
            'primary_color' => '#059669',
            'secondary_color' => '#10B981',
            'header_bg' => '#064e3b',
            'footer_bg' => '#022c22',
            'font_family' => 'Instrument Sans',
            'border_radius' => 'medium',
            'button_style' => 'rounded',
            'card_style' => 'elevated',
        ]);

        $response->assertRedirect('/admin/theme');
        $response->assertSessionHas('success');

        $theme = Theme::where('hotel_id', $this->hotel->id)->where('is_active', true)->first();
        $this->assertNotNull($theme);
        $this->assertSame('#059669', $theme->primary_color);
        $this->assertSame('#10B981', $theme->secondary_color);
        $this->assertSame('#064e3b', $theme->header_bg);
        $this->assertSame('#022c22', $theme->footer_bg);
    }

    public function test_guest_pages_receive_active_theme_and_css_variables(): void
    {
        Theme::create([
            'hotel_id' => $this->hotel->id,
            'name' => 'Green Style Guest Theme',
            'is_active' => true,
            'primary_color' => '#059669',
            'secondary_color' => '#10B981',
            'config' => [
                'header_bg' => '#064e3b',
                'footer_bg' => '#022c22',
            ],
            'font_family' => 'Instrument Sans',
            'border_radius' => 'medium',
            'button_style' => 'rounded',
            'card_style' => 'elevated',
        ]);

        $response = $this->get('/branches');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->has('theme')
            ->where('theme.primary_color', '#059669')
            ->where('theme.secondary_color', '#10B981')
            ->where('theme.header_bg', '#064e3b')
            ->where('theme.footer_bg', '#022c22')
            ->where('theme.css_variables.--color-primary', '#059669')
            ->has('theme.css_variables.--header-bg')
            ->has('theme.css_variables.--footer-bg')
            ->has('theme.css_variables.--dock-bg')
        );
    }
}
