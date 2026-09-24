<?php

namespace Tests\Feature;

use App\Actions\Pages\CreatePageAction;
use App\Models\Hotel;
use App\Models\Page;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PageHomeUniquenessTest extends TestCase
{
    use RefreshDatabase;

    protected Hotel $hotel;

    protected function setUp(): void
    {
        parent::setUp();

        $this->hotel = Hotel::create([
            'name' => 'Test Hotel', 'slug' => 'test-hotel-'.uniqid(), 'status' => 'active',
        ]);
    }

    /** @test */
    public function setting_a_second_home_page_via_the_action_auto_demotes_the_first(): void
    {
        $first = app(CreatePageAction::class)->execute($this->hotel, [
            'name' => 'Homepage', 'slug' => '', 'is_home' => true,
        ]);

        $second = app(CreatePageAction::class)->execute($this->hotel, [
            'name' => 'New Homepage', 'slug' => 'new-homepage', 'is_home' => true,
        ]);

        $this->assertFalse($first->fresh()->is_home);
        $this->assertTrue($second->fresh()->is_home);

        // Exactly one home page for this hotel, always.
        $this->assertEquals(
            1,
            Page::where('hotel_id', $this->hotel->id)->where('is_home', true)->count()
        );
    }

    /**
     * This is the actual safety net, not the application-level
     * auto-demote above: even if something bypasses CreatePageAction
     * entirely (a bug, a direct query, a future code path that forgets to
     * call the action), the database itself refuses a second is_home=true
     * row for the same hotel.
     *
     * @test
     */
    public function the_database_itself_rejects_a_second_home_page_bypassing_the_action(): void
    {
        Page::create([
            'hotel_id' => $this->hotel->id, 'name' => 'Homepage', 'slug' => '',
            'is_home' => true, 'status' => 'draft',
        ]);

        $this->expectException(QueryException::class);

        // Deliberately bypasses CreatePageAction's demote-first courtesy
        // logic, to prove the constraint - not just the action - is what
        // prevents two home pages.
        Page::create([
            'hotel_id' => $this->hotel->id, 'name' => 'Second Homepage', 'slug' => 'second',
            'is_home' => true, 'status' => 'draft',
        ]);
    }

    /** @test */
    public function two_different_hotels_can_each_have_their_own_home_page(): void
    {
        $otherHotel = Hotel::create([
            'name' => 'Other Hotel', 'slug' => 'other-hotel-'.uniqid(), 'status' => 'active',
        ]);

        app(CreatePageAction::class)->execute($this->hotel, ['name' => 'Home', 'slug' => '', 'is_home' => true]);
        app(CreatePageAction::class)->execute($otherHotel, ['name' => 'Home', 'slug' => '', 'is_home' => true]);

        // The home_marker generated column is IF(is_home, hotel_id, NULL) -
        // scoping by hotel_id is precisely what allows this: two different
        // hotel_ids as the marker value are two different unique values,
        // not a collision.
        $this->assertEquals(1, Page::where('hotel_id', $this->hotel->id)->where('is_home', true)->count());
        $this->assertEquals(1, Page::where('hotel_id', $otherHotel->id)->where('is_home', true)->count());
    }

    /** @test */
    public function non_home_pages_are_unaffected_by_the_uniqueness_constraint(): void
    {
        // Many is_home=false rows must coexist fine - home_marker is NULL
        // for all of them, and unique indexes permit unlimited NULLs.
        for ($i = 0; $i < 5; $i++) {
            Page::create([
                'hotel_id' => $this->hotel->id, 'name' => "Page {$i}", 'slug' => "page-{$i}",
                'is_home' => false, 'status' => 'draft',
            ]);
        }

        $this->assertEquals(5, Page::where('hotel_id', $this->hotel->id)->count());
    }
}
