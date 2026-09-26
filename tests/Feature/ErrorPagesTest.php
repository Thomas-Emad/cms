<?php

namespace Tests\Feature;

use App\Models\Hotel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ErrorPagesTest extends TestCase
{
    use RefreshDatabase;

    protected Hotel $hotel;

    protected User $superAdmin;

    protected User $hotelAdmin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->hotel = Hotel::create([
            'name' => 'Grand Horizon',
            'slug' => 'grand-horizon-'.uniqid(),
            'status' => 'active',
        ]);

        $this->superAdmin = User::create([
            'name' => 'Super Admin',
            'email' => 'super-'.uniqid().'@example.com',
            'password' => Hash::make('password'),
            'role' => 'super_admin',
            'status' => 'active',
        ]);

        $this->hotelAdmin = User::create([
            'hotel_id' => $this->hotel->id,
            'name' => 'Hotel Admin',
            'email' => 'admin-'.uniqid().'@example.com',
            'password' => Hash::make('password'),
            'role' => 'hotel_admin',
            'status' => 'active',
        ]);
    }

    public function test_non_existent_page_renders_inertia_error_404(): void
    {
        $response = $this->get('/some-non-existent-luxury-page-url');

        $response->assertStatus(404);
        $response->assertInertia(fn ($page) => $page
            ->component('Error')
            ->where('status', 404)
        );
    }

    public function test_forbidden_route_renders_inertia_error_403_with_authenticated_user_context(): void
    {
        // Hotel admin attempting to access super admin platform dashboard route -> 403
        $response = $this->actingAs($this->hotelAdmin)
            ->get('/admin/platform-dashboard');

        $response->assertStatus(403);
        $response->assertInertia(fn ($page) => $page
            ->component('Error')
            ->where('status', 403)
            ->has('user')
            ->where('user.email', $this->hotelAdmin->email)
        );
    }

    public function test_authenticated_user_can_log_out_successfully_from_error_context(): void
    {
        $this->actingAs($this->hotelAdmin);
        $this->assertAuthenticatedAs($this->hotelAdmin);

        $response = $this->post('/logout');

        $response->assertRedirect('/login');
        $this->assertGuest();
    }

    public function test_blade_fallback_error_views_exist_and_render_correctly(): void
    {
        // Test as authenticated user: logout form must be present
        $this->actingAs($this->hotelAdmin);
        $view403 = view('errors.403', ['exception' => null])->render();
        $this->assertStringContainsString('403', $view403);
        $this->assertStringContainsString('logout', $view403);

        $view404 = view('errors.404', ['exception' => null])->render();
        $this->assertStringContainsString('404', $view404);
    }
}
