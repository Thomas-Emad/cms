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
            ->has('branches', 5)
            ->where('branches.0.slug', 'smarttel-cairo-garden-city')
            ->where('branches.0.name', 'Smarttel Hotel Cairo')
            ->where('branches.0.address', 'Nile Corniche, Garden City, Cairo, Egypt')
            ->has('branches.0.all_photos', 7)
            ->where('branches.0.phone', '+20 2 2578 0444')
            ->where('branches.0.is_main', true)
            ->where('branches.1.slug', 'smarttel-alexandria-corniche')
            ->where('branches.1.address', 'Alexandria Corniche, Sidi Bishr, Alexandria, Egypt')
            ->has('branches.1.all_photos', 7)
            ->where('branches.2.slug', 'smarttel-sharm-el-sheikh')
            ->where('branches.2.address', 'Naama Bay, Sharm El Sheikh, South Sinai, Egypt')
            ->has('branches.2.all_photos', 9)
        );

    }

    public function test_branches_page_respects_branch_query_parameter_for_tab_selection(): void
    {
        $this->seed(HotelBranchSeeder::class);

        $response = $this->get('/branches?branch=smarttel-alexandria-corniche');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Guest/Branches/Index')
            ->where('active_branch', 'smarttel-alexandria-corniche')
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

        $branch = HotelBranch::where('slug', 'smarttel-cairo-garden-city')->first();
        $this->assertSame('Smarttel Hotel Cairo', $branch->name);
        $this->assertSame('Nile Corniche, Garden City, Cairo, Egypt', $branch->address);

        app()->setLocale('ar');
        $this->assertSame('فروعنا', __('branches.title'));
        $this->assertSame('العنوان', __('branches.address'));
        $this->assertSame('الصور', __('branches.photos'));
        $this->assertSame('الفروع', __('nav.branches'));

        $this->assertSame('فندق سمارتيل القاهرة', $branch->name);
        $this->assertSame('كورنيش النيل، جاردن سيتي، القاهرة، مصر', $branch->address);
    }
}
