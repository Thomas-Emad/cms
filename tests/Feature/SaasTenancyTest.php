<?php

namespace Tests\Feature;

use App\Models\Facility;
use App\Models\Hotel;
use App\Models\HotelBranch;
use App\Models\HotelSettings;
use App\Models\Theme;
use App\Models\User;
use App\Services\Tenancy\CurrentHotel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SaasTenancyTest extends TestCase
{
    use RefreshDatabase;

    protected Hotel $hotelA;

    protected Hotel $hotelB;

    protected User $superAdmin;

    protected User $adminA;

    protected User $adminB;

    protected function setUp(): void
    {
        parent::setUp();

        // Hotel A (Active)
        $this->hotelA = Hotel::create([
            'name' => 'Grand Cairo Resort',
            'slug' => 'grand-cairo',
            'domain' => 'cairo.resort.test',
            'status' => 'active',
            'timezone' => 'Africa/Cairo',
            'currency' => 'EGP',
        ]);

        HotelSettings::create([
            'hotel_id' => $this->hotelA->id,
            'default_locale' => 'en',
            'checkin_time' => '14:00:00',
            'checkout_time' => '12:00:00',
        ]);

        // Hotel B (Suspended)
        $this->hotelB = Hotel::create([
            'name' => 'Alexandria Coastal Palace',
            'slug' => 'alex-palace',
            'domain' => 'alex.palace.test',
            'status' => 'suspended',
            'timezone' => 'Africa/Cairo',
            'currency' => 'EGP',
        ]);

        HotelSettings::create([
            'hotel_id' => $this->hotelB->id,
            'default_locale' => 'en',
            'checkin_time' => '15:00:00',
            'checkout_time' => '11:00:00',
        ]);

        // Users
        $this->superAdmin = User::create([
            'name' => 'Platform Super Admin',
            'email' => 'superadmin@saas.test',
            'password' => Hash::make('password'),
            'hotel_id' => null,
            'role' => 'super_admin',
            'status' => 'active',
        ]);

        $this->adminA = User::create([
            'name' => 'Admin Cairo',
            'email' => 'admin@cairo.test',
            'password' => Hash::make('password'),
            'hotel_id' => $this->hotelA->id,
            'role' => 'hotel_admin',
            'status' => 'active',
        ]);

        $this->adminB = User::create([
            'name' => 'Admin Alex',
            'email' => 'admin@alex.test',
            'password' => Hash::make('password'),
            'hotel_id' => $this->hotelB->id,
            'role' => 'hotel_admin',
            'status' => 'active',
        ]);
    }

    public function test_tenant_resolution_via_custom_domain(): void
    {
        $response = $this->get('http://cairo.resort.test/facilities');

        $response->assertOk();
        $this->assertSame($this->hotelA->id, app(CurrentHotel::class)->id());
    }

    public function test_tenant_resolution_via_subdomain_slug(): void
    {
        // Host has slug as first segment
        $response = $this->get('http://grand-cairo.saas-platform.test/facilities');

        $response->assertOk();
        $this->assertSame($this->hotelA->id, app(CurrentHotel::class)->id());
    }

    public function test_unknown_domain_returns_404(): void
    {
        $response = $this->get('http://unknown-hotel.example.com/facilities');

        $response->assertNotFound();
    }

    public function test_suspended_hotel_blocks_guests_and_tenant_users_but_allows_super_admin(): void
    {
        // Guest request to suspended hotel
        $guestResponse = $this->get('http://alex.palace.test/facilities');
        $guestResponse->assertStatus(403);

        // Tenant Admin request to suspended hotel
        $tenantAdminResponse = $this->actingAs($this->adminB)
            ->get('http://alex.palace.test/admin/facilities');
        $tenantAdminResponse->assertStatus(403);

        // Super Admin request to suspended hotel is allowed to manage it
        $superAdminResponse = $this->actingAs($this->superAdmin)
            ->get('http://alex.palace.test/admin/facilities');
        $superAdminResponse->assertOk();
    }

    public function test_tenant_isolation_hotel_admin_cannot_access_another_hotel_domain(): void
    {
        // Admin A tries to access Hotel B's domain
        $response = $this->actingAs($this->adminA)
            ->get('http://alex.palace.test/admin/facilities');

        $response->assertStatus(403);
    }

    public function test_tenant_isolation_hotel_admin_cannot_view_or_modify_another_hotel_content(): void
    {
        // Set context to Hotel A
        app(CurrentHotel::class)->set($this->hotelA);

        $facilityB = Facility::create([
            'hotel_id' => $this->hotelB->id,
            'name' => 'Alex Royal Spa',
            'slug' => 'alex-royal-spa',
            'category' => 'wellness',
            'status' => 'published',
        ]);

        // Admin A trying to edit Hotel B facility
        $responseEdit = $this->actingAs($this->adminA)
            ->get("http://cairo.resort.test/admin/facilities/{$facilityB->id}/edit");

        $this->assertTrue(in_array($responseEdit->status(), [403, 404], true));

        // Admin A trying to update Hotel B facility
        $responseUpdate = $this->actingAs($this->adminA)
            ->put("http://cairo.resort.test/admin/facilities/{$facilityB->id}", [
                'name' => 'Hacked Spa',
                'slug' => 'hacked-spa',
                'category' => 'wellness',
                'status' => 'published',
            ]);

        $this->assertTrue(in_array($responseUpdate->status(), [403, 404], true));
    }

    public function test_hotel_admin_cannot_access_super_admin_customers_routes(): void
    {
        $response = $this->actingAs($this->adminA)->get('/admin/customers');
        $response->assertStatus(403);

        $responseCreate = $this->actingAs($this->adminA)->get('/admin/customers/create');
        $responseCreate->assertStatus(403);

        $responsePost = $this->actingAs($this->adminA)->post('/admin/customers', [
            'name' => 'Unauthorized Hotel',
            'slug' => 'unauthorized-hotel',
            'status' => 'active',
            'admin_name' => 'Hacker',
            'admin_email' => 'hacker@example.com',
            'admin_password' => 'password123',
        ]);
        $responsePost->assertStatus(403);
    }

    public function test_super_admin_can_view_customers_list_and_search(): void
    {
        $response = $this->actingAs($this->superAdmin)->get('/admin/customers');
        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Admin/Customers/Index')
            ->has('customers.data', 2)
        );

        // Search by slug
        $searchResponse = $this->actingAs($this->superAdmin)->get('/admin/customers?q=grand-cairo');
        $searchResponse->assertOk();
        $searchResponse->assertInertia(fn ($page) => $page
            ->where('customers.data.0.slug', 'grand-cairo')
        );
    }

    public function test_super_admin_can_create_hotel_with_settings_theme_and_admin_in_transaction(): void
    {
        $payload = [
            'name' => 'Hurghada Red Sea Resort',
            'slug' => 'hurghada-resort',
            'domain' => 'hurghada.redsea.test',
            'status' => 'active',
            'contact_email' => 'contact@hurghada.test',
            'contact_phone' => '+20 65 344 0000',
            'address' => 'Sahl Hasheesh, Hurghada, Egypt',
            'timezone' => 'Africa/Cairo',
            'currency' => 'EGP',
            'admin_name' => 'General Manager',
            'admin_email' => 'gm@hurghada.test',
            'admin_password' => 'secret12345',
            'default_locale' => 'en',
            'checkin_time' => '14:00',
            'checkout_time' => '12:00',
        ];

        $response = $this->actingAs($this->superAdmin)->post('/admin/customers', $payload);

        $newHotel = Hotel::where('slug', 'hurghada-resort')->first();
        $this->assertNotNull($newHotel);

        $response->assertRedirect("/admin/customers/{$newHotel->id}");

        // Assert relationships were created
        $this->assertDatabaseHas('hotel_settings', ['hotel_id' => $newHotel->id]);
        $this->assertDatabaseHas('themes', ['hotel_id' => $newHotel->id, 'is_active' => true]);
        $this->assertDatabaseHas('users', [
            'hotel_id' => $newHotel->id,
            'email' => 'gm@hurghada.test',
            'role' => 'hotel_admin',
        ]);
    }

    public function test_super_admin_can_update_domain_and_resolution_updates(): void
    {
        // 1. Initial domain resolves
        $this->get('http://cairo.resort.test/facilities')
            ->assertOk();

        // 2. Super admin updates domain
        $response = $this->actingAs($this->superAdmin)->patch("/admin/customers/{$this->hotelA->id}/domain", [
            'domain' => 'new-cairo-luxury.test',
        ]);
        $response->assertRedirect();

        $this->hotelA->refresh();
        $this->assertSame('new-cairo-luxury.test', $this->hotelA->domain);

        // Clear singleton cache
        app(CurrentHotel::class)->clear();

        // 3. New domain resolves to Hotel A
        $this->get('http://new-cairo-luxury.test/facilities')
            ->assertOk();
        $this->assertSame($this->hotelA->id, app(CurrentHotel::class)->id());

        app(CurrentHotel::class)->clear();

        // 4. Old domain no longer resolves
        $this->get('http://cairo.resort.test/facilities')
            ->assertNotFound();
    }

    public function test_super_admin_can_suspend_and_reactivate_hotel(): void
    {
        // Suspend Hotel A
        $this->actingAs($this->superAdmin)->patch("/admin/customers/{$this->hotelA->id}/status", [
            'status' => 'suspended',
        ]);

        $this->hotelA->refresh();
        $this->assertSame('suspended', $this->hotelA->status);

        // Reactivate Hotel B
        $this->actingAs($this->superAdmin)->patch("/admin/customers/{$this->hotelB->id}/status", [
            'status' => 'active',
        ]);

        $this->hotelB->refresh();
        $this->assertSame('active', $this->hotelB->status);
    }

    public function test_super_admin_can_add_user_to_hotel(): void
    {
        $response = $this->actingAs($this->superAdmin)->post("/admin/customers/{$this->hotelA->id}/users", [
            'name' => 'Operations Manager',
            'email' => 'ops@cairo.test',
            'password' => 'password123',
            'role' => 'hotel_admin',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('users', [
            'hotel_id' => $this->hotelA->id,
            'email' => 'ops@cairo.test',
            'role' => 'hotel_admin',
        ]);
    }

    public function test_tenant_resolution_via_branch_domain(): void
    {
        $branch = HotelBranch::create([
            'hotel_id' => $this->hotelA->id,
            'name' => 'Giza Nile Branch',
            'slug' => 'giza-nile',
            'domain' => 'giza.branch.test',
            'city' => 'Giza',
            'status' => 'published',
            'is_main' => false,
        ]);

        $response = $this->get('http://giza.branch.test/facilities');

        $response->assertOk();
        $this->assertSame($this->hotelA->id, app(CurrentHotel::class)->id());
        $this->assertTrue(app(CurrentHotel::class)->hasBranch());
        $this->assertSame($branch->id, app(CurrentHotel::class)->branch()->id);
    }

    public function test_super_admin_redirected_to_platform_dashboard_from_admin_dashboard(): void
    {
        $response = $this->actingAs($this->superAdmin)
            ->get('http://cairo.resort.test/admin/dashboard');

        $response->assertRedirect(route('admin.platform.dashboard'));
    }

    public function test_super_admin_can_view_platform_dashboard(): void
    {
        $response = $this->actingAs($this->superAdmin)
            ->get(route('admin.platform.dashboard'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Admin/Platform/Dashboard')
            ->has('metrics')
            ->has('recent_hotels')
            ->has('branches_with_domains')
        );
    }

    public function test_hotel_admin_cannot_access_platform_dashboard(): void
    {
        $response = $this->actingAs($this->adminA)
            ->get(route('admin.platform.dashboard'));

        $response->assertForbidden();
    }

    public function test_branch_specific_content_and_hotel_wide_content_resolution(): void
    {
        $branch1 = HotelBranch::create([
            'hotel_id' => $this->hotelA->id,
            'name' => 'Giza Nile Branch',
            'slug' => 'giza-nile',
            'domain' => 'giza.branch.test',
            'city' => 'Giza',
            'status' => 'published',
            'is_main' => false,
        ]);

        $branch2 = HotelBranch::create([
            'hotel_id' => $this->hotelA->id,
            'name' => 'Red Sea Branch',
            'slug' => 'red-sea',
            'domain' => 'redsea.branch.test',
            'city' => 'Hurghada',
            'status' => 'published',
            'is_main' => false,
        ]);

        $globalFacility = Facility::create([
            'hotel_id' => $this->hotelA->id,
            'hotel_branch_id' => null,
            'name' => 'Global Wellness Spa',
            'slug' => 'global-wellness-spa',
            'category' => 'wellness',
            'status' => 'published',
        ]);

        $branch1Facility = Facility::create([
            'hotel_id' => $this->hotelA->id,
            'hotel_branch_id' => $branch1->id,
            'name' => 'Giza Rooftop Pool',
            'slug' => 'giza-rooftop-pool',
            'category' => 'pool',
            'status' => 'published',
        ]);

        $branch2Facility = Facility::create([
            'hotel_id' => $this->hotelA->id,
            'hotel_branch_id' => $branch2->id,
            'name' => 'Red Sea Diving Club',
            'slug' => 'red-sea-diving-club',
            'category' => 'beach',
            'status' => 'published',
        ]);

        // Branch 1 guest page: sees global facility and branch 1 facility, but NOT branch 2
        $response = $this->get('http://giza.branch.test/facilities');
        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Guest/Facilities/Index')
            ->where('facilities.0.slug', fn ($slug) => in_array($slug, ['global-wellness-spa', 'giza-rooftop-pool']))
            ->has('facilities', 2)
        );

        // Branch 2 guest page: sees global facility and branch 2 facility, but NOT branch 1
        $response2 = $this->get('http://redsea.branch.test/facilities');
        $response2->assertOk();
        $response2->assertInertia(fn ($page) => $page
            ->component('Guest/Facilities/Index')
            ->has('facilities', 2)
        );
    }

    public function test_branch_theme_override_and_fallback(): void
    {
        $branch = HotelBranch::create([
            'hotel_id' => $this->hotelA->id,
            'name' => 'Zamalek Boutique',
            'slug' => 'zamalek-boutique',
            'domain' => 'zamalek.branch.test',
            'city' => 'Cairo',
            'status' => 'published',
            'is_main' => false,
        ]);

        // 1. Hotel Master Theme (#112233)
        $masterTheme = Theme::create([
            'hotel_id' => $this->hotelA->id,
            'hotel_branch_id' => null,
            'name' => 'Hotel Master Theme',
            'primary_color' => '#112233',
            'secondary_color' => '#445566',
            'is_active' => true,
        ]);

        // Guest on Zamalek branch initially inherits master theme
        $response = $this->get('http://zamalek.branch.test/facilities');
        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->where('theme.primary_color', '#112233')
        );

        // 2. Admin creates custom theme for Zamalek branch (#998877)
        $this->actingAs($this->adminA)
            ->put('http://cairo.resort.test/admin/theme', [
                'hotel_branch_id' => $branch->id,
                'name' => 'Zamalek Custom Theme',
                'primary_color' => '#998877',
                'secondary_color' => '#665544',
            ])
            ->assertRedirect(route('admin.theme.edit', ['branch_id' => $branch->id]));

        // Guest on Zamalek branch now receives custom theme
        $response = $this->get('http://zamalek.branch.test/facilities');
        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->where('theme.primary_color', '#998877')
        );

        // Guest on master hotel domain still receives master theme (#112233)
        $masterResponse = $this->get('http://cairo.resort.test/facilities');
        $masterResponse->assertOk();
        $masterResponse->assertInertia(fn ($page) => $page
            ->where('theme.primary_color', '#112233')
        );

        // 3. Admin resets Zamalek branch theme
        $this->actingAs($this->adminA)
            ->delete('http://cairo.resort.test/admin/theme/branch-reset', [
                'hotel_branch_id' => $branch->id,
            ])
            ->assertRedirect(route('admin.theme.edit', ['branch_id' => $branch->id]));

        // Guest on Zamalek branch now falls back to master theme (#112233)
        $responseAfterReset = $this->get('http://zamalek.branch.test/facilities');
        $responseAfterReset->assertOk();
        $responseAfterReset->assertInertia(fn ($page) => $page
            ->where('theme.primary_color', '#112233')
        );
    }

    public function test_hotel_admin_can_assign_content_to_branch(): void
    {
        $branch = HotelBranch::create([
            'hotel_id' => $this->hotelA->id,
            'name' => 'New Cairo Branch',
            'slug' => 'new-cairo',
            'city' => 'New Cairo',
            'status' => 'published',
            'is_main' => false,
        ]);

        $response = $this->actingAs($this->adminA)
            ->post('http://cairo.resort.test/admin/facilities', [
                'hotel_branch_id' => $branch->id,
                'name' => 'Sky Lounge',
                'slug' => 'sky-lounge',
                'category' => 'other',
                'status' => 'published',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('facilities', [
            'hotel_id' => $this->hotelA->id,
            'hotel_branch_id' => $branch->id,
            'slug' => 'sky-lounge',
        ]);
    }
}
