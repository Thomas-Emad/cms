<?php

namespace Tests\Feature;

use App\Models\Hotel;
use App\Models\HotelSettings;
use App\Services\Layout\GuestLayoutConfig;
use App\Services\Layout\GuestLayoutStore;
use App\Services\Tenancy\CurrentHotel;
use App\Services\Weather\WeatherService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class WeatherTest extends TestCase
{
    use RefreshDatabase;

    protected Hotel $hotel;

    protected function setUp(): void
    {
        parent::setUp();

        $this->hotel = Hotel::create([
            'name' => 'Hilton Grand Horizon',
            'slug' => 'hilton-grand-horizon',
            'status' => 'active',
            'address' => 'Horizon Bay Resort',
            'timezone' => 'Asia/Dubai',
        ]);

        app(CurrentHotel::class)->set($this->hotel);
    }

    /**
     * Helper to return standard Open-Meteo mock response.
     */
    protected function fakeOpenMeteoSuccess(): void
    {
        Http::fake([
            'api.open-meteo.com/v1/forecast*' => Http::response([
                'latitude' => 25.2048,
                'longitude' => 55.2708,
                'timezone' => 'Asia/Dubai',
                'daily_units' => [
                    'time' => 'iso8601',
                    'weather_code' => 'wmo code',
                    'temperature_2m_max' => '°C',
                    'temperature_2m_min' => '°C',
                    'apparent_temperature_max' => '°C',
                    'apparent_temperature_min' => '°C',
                    'precipitation_sum' => 'mm',
                    'wind_speed_10m_max' => 'km/h',
                ],
                'daily' => [
                    'time' => [
                        '2026-09-19',
                        '2026-09-20',
                        '2026-09-21',
                        '2026-09-22',
                        '2026-09-23',
                    ],
                    'weather_code' => [0, 1, 2, 61, 0],
                    'temperature_2m_max' => [38.5, 39.2, 37.0, 32.5, 36.8],
                    'temperature_2m_min' => [27.0, 28.1, 26.5, 24.0, 26.2],
                    'apparent_temperature_max' => [42.0, 43.5, 40.1, 35.0, 39.8],
                    'apparent_temperature_min' => [31.5, 32.0, 30.2, 28.0, 30.5],
                    'precipitation_sum' => [0.0, 0.0, 0.0, 4.2, 0.0],
                    'wind_speed_10m_max' => [12.5, 14.0, 18.2, 22.5, 13.0],
                ],
            ], 200),
        ]);
    }

    public function test_weather_page_is_accessible_and_renders_inertia_weather_index(): void
    {
        $this->fakeOpenMeteoSuccess();

        $response = $this->get('/weather');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Guest/Weather/Index')
            ->has('weather')
            ->where('weather.status', 'ok')
            ->has('weather.days', 5)
            ->where('weather.days.0.date', '2026-09-19')
            ->where('weather.days.4.date', '2026-09-23')
            ->where('weather.days.0.temp_max', 38.5)
            ->where('weather.days.0.temp_min', fn ($v) => (float) $v === 27.0)
            ->where('weather.days.0.condition_key', 'clear_sky')
            ->where('weather.days.0.icon', 'sun')
            ->where('weather.days.3.condition_key', 'rain')
            ->where('weather.days.3.precipitation', 4.2)
        );
    }

    public function test_weather_handles_external_api_failure_gracefully_without_crashing(): void
    {
        Http::fake([
            'api.open-meteo.com/v1/forecast*' => Http::response(null, 500),
        ]);

        $response = $this->get('/weather');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Guest/Weather/Index')
            ->has('weather')
            ->where('weather.status', 'error')
            ->has('weather.days', 0)
        );
    }

    public function test_weather_appears_in_guest_layout_defaults_next_to_meeting_room(): void
    {
        $defaults = GuestLayoutConfig::defaults();
        $items = $defaults['items'];

        $meetingRoomIndex = null;
        $weatherIndex = null;

        foreach ($items as $idx => $item) {
            if ($item['href'] === '/meeting-rooms') {
                $meetingRoomIndex = $idx;
            }
            if ($item['href'] === '/weather') {
                $weatherIndex = $idx;
            }
        }

        $this->assertNotNull($meetingRoomIndex, 'Meeting Room must be present in layout defaults');
        $this->assertNotNull($weatherIndex, 'Weather must be present in layout defaults');
        $this->assertEquals($meetingRoomIndex + 1, $weatherIndex, 'Weather must be positioned immediately next to Meeting Room');
    }

    public function test_both_guest_layout_templates_render_weather_nav_item(): void
    {
        $this->fakeOpenMeteoSuccess();

        // 1. Classic shell
        $responseClassic = $this->get('/weather');
        $responseClassic->assertStatus(200);
        $responseClassic->assertInertia(fn ($page) => $page
            ->where('guestLayout.template', 'classic')
            ->where('guestLayout.items.7.href', '/weather')
        );

        // 2. TV shell
        app(GuestLayoutStore::class)->save($this->hotel->id, array_merge(GuestLayoutConfig::defaults(), [
            'template' => 'tv',
        ]));

        $responseTv = $this->get('/weather');
        $responseTv->assertStatus(200);
        $responseTv->assertInertia(fn ($page) => $page
            ->where('guestLayout.template', 'tv')
            ->where('guestLayout.items.7.href', '/weather')
        );
    }

    public function test_weather_location_reads_from_hotel_settings_metadata_if_present(): void
    {
        $this->fakeOpenMeteoSuccess();

        HotelSettings::updateOrCreate(
            ['hotel_id' => $this->hotel->id],
            [
                'metadata' => [
                    'weather' => [
                        'city' => 'Alexandria Coast',
                        'latitude' => 31.2001,
                        'longitude' => 29.9187,
                    ],
                ],
            ]
        );

        $response = $this->get('/weather');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->where('weather.location.city', 'Alexandria Coast')
            ->where('weather.location.latitude', 31.2001)
            ->where('weather.location.longitude', 29.9187)
        );
    }

    public function test_weather_translations_exist_for_en_and_ar(): void
    {
        app()->setLocale('en');
        $this->assertSame('Weather', __('weather.title'));
        $this->assertSame('Clear sky', __('weather.conditions.clear_sky'));
        $this->assertSame('Weather', __('nav.weather'));

        app()->setLocale('ar');
        $this->assertSame('الطقس', __('weather.title'));
        $this->assertSame('سماء صافية', __('weather.conditions.clear_sky'));
        $this->assertSame('الطقس', __('nav.weather'));
    }

    public function test_weather_provides_hotel_branches_and_allows_switching_branches(): void
    {
        $this->fakeOpenMeteoSuccess();

        // Default branch
        $response = $this->get('/weather');
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Guest/Weather/Index')
            ->has('weather.branches', 3)
            ->where('weather.current_branch_id', 'horizon-bay')
            ->where('weather.location.branch_name', 'Horizon Bay (Beach Resort)')
        );

        // Switch to Downtown branch
        $responseDowntown = $this->get('/weather?branch=downtown-city');
        $responseDowntown->assertStatus(200);
        $responseDowntown->assertInertia(fn ($page) => $page
            ->where('weather.current_branch_id', 'downtown-city')
            ->where('weather.location.branch_name', 'Downtown Skyline Branch')
            ->where('weather.location.latitude', 25.1972)
            ->where('weather.location.longitude', 25.2744 ? 55.2744 : 55.2744)
        );

        // Unknown branch falls back to default branch
        $responseFallback = $this->get('/weather?branch=unknown-branch-slug');
        $responseFallback->assertStatus(200);
        $responseFallback->assertInertia(fn ($page) => $page
            ->where('weather.current_branch_id', 'horizon-bay')
        );
    }

    public function test_weather_custom_branches_in_hotel_settings_metadata(): void
    {
        $this->fakeOpenMeteoSuccess();

        HotelSettings::updateOrCreate(
            ['hotel_id' => $this->hotel->id],
            [
                'metadata' => [
                    'weather' => [
                        'branches' => [
                            [
                                'id' => 'cairo-tower',
                                'name' => 'Cairo Nile Branch',
                                'city' => 'Cairo',
                                'latitude' => 30.0444,
                                'longitude' => 31.2357,
                                'is_default' => true,
                            ],
                            [
                                'id' => 'red-sea',
                                'name' => 'Red Sea Resort',
                                'city' => 'Hurghada',
                                'latitude' => 27.2579,
                                'longitude' => 33.8116,
                                'is_default' => false,
                            ],
                        ],
                    ],
                ],
            ]
        );

        $response = $this->get('/weather');
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->has('weather.branches', 2)
            ->where('weather.current_branch_id', 'cairo-tower')
            ->where('weather.location.branch_name', 'Cairo Nile Branch')
            ->where('weather.location.latitude', 30.0444)
        );

        $responseRedSea = $this->get('/weather?branch=red-sea');
        $responseRedSea->assertStatus(200);
        $responseRedSea->assertInertia(fn ($page) => $page
            ->where('weather.current_branch_id', 'red-sea')
            ->where('weather.location.branch_name', 'Red Sea Resort')
            ->where('weather.location.latitude', 27.2579)
        );
    }

    public function test_real_open_meteo_api_integration(): void
    {
        // Real network call to Open-Meteo without mocking
        $service = new WeatherService;
        $data = $service->getPastDaysWeather($this->hotel, 'horizon-bay', 5);

        $this->assertSame('ok', $data['status']);
        $this->assertCount(5, $data['days']);
        $this->assertNotEmpty($data['days'][0]['date']);
        $this->assertIsNumeric($data['days'][0]['temp_max']);
        $this->assertIsNumeric($data['days'][0]['temp_min']);
        $this->assertNotEmpty($data['days'][0]['condition_key']);
        $this->assertNotEmpty($data['days'][0]['icon']);
        $this->assertNotEmpty($data['branches']);
    }
}
