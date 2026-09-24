<?php

namespace Tests\Feature;

use App\Models\Hotel;
use App\Models\HotelBranch;
use App\Services\Layout\GuestLayoutConfig;
use App\Services\Tenancy\CurrentHotel;
use Database\Seeders\HotelBranchSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BranchTest extends TestCase
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
            'address' => '1 Horizon Bay Drive',
            'timezone' => 'Asia/Dubai',
        ]);

        app(CurrentHotel::class)->set($this->hotel);
    }

    public function test_branches_page_is_accessible_and_renders_branches_with_photos_and_addresses(): void
    {
        $this->seed(HotelBranchSeeder::class);

        $response = $this->get('/branches');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Guest/Branches/Index')
            ->has('branches', 3)
            ->where('branches.0.slug', 'horizon-bay-resort')
            ->where('branches.0.name', 'Horizon Bay Resort & Marina')
            ->where('branches.0.address', '1 Horizon Bay Drive, Coastal Boulevard')
            ->has('branches.0.all_photos', 4)
            ->where('branches.0.phone', '+1 555 010 2020')
            ->where('branches.0.is_main', true)
            ->where('branches.1.slug', 'downtown-towers')
            ->where('branches.1.address', '450 Financial Avenue, Skyline District')
            ->has('branches.1.all_photos', 4)
            ->where('branches.2.slug', 'palm-island-oasis')
            ->where('branches.2.address', 'Crescent West 12, The Palm Archipelago')
            ->has('branches.2.all_photos', 4)
        );
    }

    public function test_branches_page_respects_branch_query_parameter_for_tab_selection(): void
    {
        $this->seed(HotelBranchSeeder::class);

        $response = $this->get('/branches?branch=downtown-towers');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Guest/Branches/Index')
            ->where('active_branch', 'downtown-towers')
        );
    }

    public function test_branches_appear_in_guest_layout_defaults(): void
    {
        $defaults = GuestLayoutConfig::defaults();
        $items = $defaults['items'];

        $branchesItem = null;
        foreach ($items as $item) {
            if ($item['href'] === '/branches') {
                $branchesItem = $item;
                break;
            }
        }

        $this->assertNotNull($branchesItem, 'Branches must be present in layout defaults');
        $this->assertSame('Branches', $branchesItem['label']);
        $this->assertTrue($branchesItem['visible']);
    }

    public function test_branch_translations_work_in_english_and_arabic(): void
    {
        $this->seed(HotelBranchSeeder::class);

        app()->setLocale('en');
        $this->assertSame('Our Branches', __('branches.title'));
        $this->assertSame('Address', __('branches.address'));
        $this->assertSame('Photos', __('branches.photos'));
        $this->assertSame('Branches', __('nav.branches'));

        $branch = HotelBranch::where('slug', 'horizon-bay-resort')->first();
        $this->assertSame('Horizon Bay Resort & Marina', $branch->name);
        $this->assertSame('1 Horizon Bay Drive, Coastal Boulevard', $branch->address);

        app()->setLocale('ar');
        $this->assertSame('فروعنا', __('branches.title'));
        $this->assertSame('العنوان', __('branches.address'));
        $this->assertSame('الصور', __('branches.photos'));
        $this->assertSame('الفروع', __('nav.branches'));

        $this->assertSame('منتجع ومارينا هورايزون باي', $branch->name);
        $this->assertSame('١ طريق هورايزون باي، شارع الساحل', $branch->address);
    }
}
