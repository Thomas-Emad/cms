<?php

namespace Tests\Feature;

use App\Models\Hotel;
use App\Models\HotelBranch;
use App\Models\User;
use App\Services\Tenancy\CurrentHotel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminBranchTest extends TestCase
{
    use RefreshDatabase;

    protected Hotel $hotelA;

    protected Hotel $hotelB;

    protected User $adminA;

    protected User $adminB;

    protected function setUp(): void
    {
        parent::setUp();

        $this->hotelA = Hotel::create([
            'name' => 'Smarttel Hotel Cairo',
            'slug' => 'smarttel-hotel-cairo',
            'status' => 'active',
            'timezone' => 'Africa/Cairo',
        ]);

        $this->hotelB = Hotel::create([
            'name' => 'Other Hotel',
            'slug' => 'other-hotel-'.uniqid(),
            'status' => 'active',
            'timezone' => 'Africa/Cairo',
        ]);

        $this->adminA = User::create([
            'hotel_id' => $this->hotelA->id,
            'role' => 'hotel_admin',
            'status' => 'active',
            'name' => 'Admin Cairo',
            'email' => 'admin-cairo@example.com',
            'password' => Hash::make('password'),
        ]);

        $this->adminB = User::create([
            'hotel_id' => $this->hotelB->id,
            'role' => 'hotel_admin',
            'status' => 'active',
            'name' => 'Admin Other',
            'email' => 'admin-other@example.com',
            'password' => Hash::make('password'),
        ]);

        app(CurrentHotel::class)->set($this->hotelA);
    }

    public function test_guest_is_redirected_from_admin_branches(): void
    {
        $response = $this->get('/admin/branches');
        $response->assertRedirect('/login');
    }

    public function test_admin_can_view_branches_index_scoped_to_current_hotel(): void
    {
        $branchA = HotelBranch::create([
            'hotel_id' => $this->hotelA->id,
            'name' => 'Cairo Garden City',
            'slug' => 'cairo-garden-city',
            'city' => 'Cairo',
            'status' => 'published',
            'is_main' => true,
        ]);

        $branchB = HotelBranch::create([
            'hotel_id' => $this->hotelB->id,
            'name' => 'Foreign Branch',
            'slug' => 'foreign-branch',
            'city' => 'Abroad',
            'status' => 'published',
            'is_main' => true,
        ]);

        $response = $this->actingAs($this->adminA)->get('/admin/branches');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Admin/Branches/Index')
            ->has('branches.data', 1)
            ->where('branches.data.0.name', 'Cairo Garden City')
            ->where('branches.data.0.slug', 'cairo-garden-city')
            ->where('branches.data.0.is_main', true)
        );
    }

    public function test_admin_can_view_create_branch_page(): void
    {
        $response = $this->actingAs($this->adminA)->get('/admin/branches/create');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Admin/Branches/Edit')
            ->where('branch', null)
        );
    }

    public function test_admin_can_create_branch_with_valid_data_and_translations(): void
    {
        $payload = [
            'name' => 'Smarttel Alexandria Corniche',
            'slug' => 'smarttel-alexandria-corniche',
            'city' => 'Alexandria, Egypt',
            'address' => '544 El-Gaish Road, Sidi Bishr',
            'phone' => '+20 3 547 7799',
            'email' => 'alex@smarttelhotel.com',
            'short_description' => 'Beachfront resort on the Mediterranean.',
            'description' => 'Stunning Mediterranean retreat with private beach club.',
            'cover_image_url' => 'https://images.unsplash.com/photo-1507525428034',
            'gallery_urls' => ['https://images.unsplash.com/photo-1', 'https://images.unsplash.com/photo-2'],
            'features' => ['Private Beach', 'Outdoor Pool', 'Seafood Restaurant'],
            'latitude' => 31.2001,
            'longitude' => 29.9187,
            'status' => 'published',
            'is_main' => true,
            'sort_order' => 2,
            'translations' => [
                'ar' => [
                    'name' => 'فندق سمارتيل الإسكندرية (الكورنيش)',
                    'city' => 'الإسكندرية، مصر',
                    'address' => '٥٤٤ طريق الجيش، سيدي بشر',
                    'short_description' => 'منتجع شاطئي على البحر المتوسط.',
                    'description' => 'ملاذ شاطئي رائع مع نادي شاطئ خاص.',
                ],
            ],
        ];

        $response = $this->actingAs($this->adminA)->post('/admin/branches', $payload);

        $branch = HotelBranch::where('hotel_id', $this->hotelA->id)
            ->where('slug', 'smarttel-alexandria-corniche')
            ->first();

        $this->assertNotNull($branch);
        $response->assertRedirect("/admin/branches/{$branch->id}/edit");
        $response->assertSessionHas('success');

        $this->assertSame('Smarttel Alexandria Corniche', $branch->name);
        $this->assertSame('Alexandria, Egypt', $branch->city);
        $this->assertSame(['Private Beach', 'Outdoor Pool', 'Seafood Restaurant'], $branch->features);
        $this->assertTrue($branch->is_main);

        // Check translations
        app()->setLocale('ar');
        $this->assertSame('فندق سمارتيل الإسكندرية (الكورنيش)', $branch->name);
        $this->assertSame('الإسكندرية، مصر', $branch->city);
    }

    public function test_admin_can_update_branch(): void
    {
        $branch = HotelBranch::create([
            'hotel_id' => $this->hotelA->id,
            'name' => 'Old Name',
            'slug' => 'old-slug',
            'city' => 'Old City',
            'status' => 'draft',
            'is_main' => false,
        ]);

        $response = $this->actingAs($this->adminA)->put("/admin/branches/{$branch->id}", [
            'name' => 'Updated Name',
            'slug' => 'updated-slug',
            'city' => 'Cairo',
            'status' => 'published',
            'is_main' => false,
            'features' => ['Wifi', 'Pool'],
        ]);

        $response->assertRedirect("/admin/branches/{$branch->id}/edit");
        $response->assertSessionHas('success');

        $branch->refresh();
        $this->assertSame('Updated Name', $branch->name);
        $this->assertSame('updated-slug', $branch->slug);
        $this->assertSame('Cairo', $branch->city);
        $this->assertSame('published', $branch->status);
        $this->assertSame(['Wifi', 'Pool'], $branch->features);
    }

    public function test_setting_branch_as_main_unsets_other_main_branches(): void
    {
        $branch1 = HotelBranch::create([
            'hotel_id' => $this->hotelA->id,
            'name' => 'Branch 1',
            'slug' => 'branch-1',
            'is_main' => true,
            'status' => 'published',
        ]);

        $branch2 = HotelBranch::create([
            'hotel_id' => $this->hotelA->id,
            'name' => 'Branch 2',
            'slug' => 'branch-2',
            'is_main' => false,
            'status' => 'published',
        ]);

        // Update branch 2 to be main
        $this->actingAs($this->adminA)->put("/admin/branches/{$branch2->id}", [
            'name' => 'Branch 2',
            'slug' => 'branch-2',
            'is_main' => true,
            'status' => 'published',
        ]);

        $branch1->refresh();
        $branch2->refresh();

        $this->assertFalse($branch1->is_main);
        $this->assertTrue($branch2->is_main);
    }

    public function test_admin_can_delete_branch(): void
    {
        $branch = HotelBranch::create([
            'hotel_id' => $this->hotelA->id,
            'name' => 'Branch to Delete',
            'slug' => 'branch-delete',
            'status' => 'published',
        ]);

        $response = $this->actingAs($this->adminA)->delete("/admin/branches/{$branch->id}");

        $response->assertRedirect('/admin/branches');
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('hotel_branches', ['id' => $branch->id]);
    }

    public function test_admin_cannot_edit_or_delete_branch_of_another_hotel(): void
    {
        $branchB = HotelBranch::create([
            'hotel_id' => $this->hotelB->id,
            'name' => 'Hotel B Branch',
            'slug' => 'hotel-b-branch',
            'status' => 'published',
        ]);

        // Trying to view edit page of hotel B branch as admin A
        $responseEdit = $this->actingAs($this->adminA)->get("/admin/branches/{$branchB->id}/edit");
        $this->assertTrue(in_array($responseEdit->status(), [403, 404], true));

        // Trying to update hotel B branch as admin A
        $responseUpdate = $this->actingAs($this->adminA)->put("/admin/branches/{$branchB->id}", [
            'name' => 'Hacked Name',
            'slug' => 'hacked-slug',
            'status' => 'published',
        ]);
        $this->assertTrue(in_array($responseUpdate->status(), [403, 404], true));

        // Trying to delete hotel B branch as admin A
        $responseDelete = $this->actingAs($this->adminA)->delete("/admin/branches/{$branchB->id}");
        $this->assertTrue(in_array($responseDelete->status(), [403, 404], true));
    }
}
