<?php

namespace Tests\Feature;

use App\Models\Hotel;
use App\Models\InfoEntry;
use App\Models\Media;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * WRITTEN BUT NOT EXECUTED: Laravel/Composer are not installable in the
 * sandbox this was authored in (same caveat as every PHPUnit file in this
 * project). Run with `php artisan test --filter=GuestScreenContentTest`.
 */
class GuestScreenContentTest extends TestCase
{
    use RefreshDatabase;

    protected Hotel $hotelA;
    protected Hotel $hotelB;
    protected User $adminA;

    protected function setUp(): void
    {
        parent::setUp();

        $this->hotelA = Hotel::create(['name' => 'Hotel A', 'slug' => 'hotel-a-' . uniqid(), 'status' => 'active']);
        $this->hotelB = Hotel::create(['name' => 'Hotel B', 'slug' => 'hotel-b-' . uniqid(), 'status' => 'active']);

        $this->adminA = User::create([
            'hotel_id' => $this->hotelA->id, 'role' => 'hotel_admin', 'status' => 'active',
            'name' => 'Admin A', 'email' => 'a-' . uniqid() . '@example.com', 'password' => Hash::make('password'),
        ]);
    }

    private function room(Hotel $hotel, string $slug = 'deluxe', string $status = 'published'): Room
    {
        return Room::create(['hotel_id' => $hotel->id, 'name' => ucfirst($slug), 'slug' => $slug, 'status' => $status]);
    }

    /* ---------------- info entries (Timing / Short Calls) ---------------- */

    /** @test */
    public function saving_info_entries_creates_updates_reorders_and_deletes(): void
    {
        $keep = InfoEntry::create(['hotel_id' => $this->hotelA->id, 'kind' => 'timing', 'group' => 'Hotel', 'label' => 'Check-in', 'value' => '14:00', 'sort_order' => 0]);
        InfoEntry::create(['hotel_id' => $this->hotelA->id, 'kind' => 'timing', 'group' => 'Hotel', 'label' => 'Gone', 'value' => 'x', 'sort_order' => 1]);

        $this->actingAs($this->adminA)->put('/admin/timing', ['entries' => [
            ['id' => null, 'group' => 'Pool', 'label' => 'Pool', 'value' => '07:00 - 20:00'],
            ['id' => $keep->id, 'group' => 'Hotel', 'label' => 'Check-in', 'value' => '15:00'],
        ]])->assertRedirect();

        $rows = InfoEntry::withoutGlobalScopes()->where('hotel_id', $this->hotelA->id)->where('kind', 'timing')->orderBy('sort_order')->get();
        $this->assertSame(['Pool', 'Check-in'], $rows->pluck('label')->all());   // new first, order follows payload
        $this->assertSame('15:00', $rows->last()->value);                            // updated in place
        $this->assertSame($keep->id, $rows->last()->id);                             // same row, not recreated
    }

    /** @test */
    public function saving_never_touches_another_hotels_entries_even_if_their_id_is_submitted(): void
    {
        $theirs = InfoEntry::create(['hotel_id' => $this->hotelB->id, 'kind' => 'short_call', 'label' => 'Theirs', 'value' => '99', 'sort_order' => 0]);

        $this->actingAs($this->adminA)->put('/admin/short-calls', ['entries' => [
            ['id' => $theirs->id, 'label' => 'Hijack', 'value' => '1'],
        ]])->assertRedirect();

        $this->assertSame('Theirs', $theirs->fresh()->label);
        $this->assertSame(1, InfoEntry::withoutGlobalScopes()->where('hotel_id', $this->hotelA->id)->count());
    }

    /** @test */
    public function an_empty_list_clears_the_kind_and_blank_labels_are_rejected(): void
    {
        InfoEntry::create(['hotel_id' => $this->hotelA->id, 'kind' => 'short_call', 'label' => 'IT', 'value' => '15', 'sort_order' => 0]);

        $this->actingAs($this->adminA)->put('/admin/short-calls', ['entries' => []])->assertRedirect();
        $this->assertSame(0, InfoEntry::withoutGlobalScopes()->where('hotel_id', $this->hotelA->id)->count());

        $this->actingAs($this->adminA)->put('/admin/short-calls', ['entries' => [['label' => '', 'value' => '15']]])
            ->assertSessionHasErrors('entries.0.label');
    }

    /** @test */
    public function guest_timing_page_groups_entries_in_admin_order(): void
    {
        foreach ([['Hotel', 'Check-in', '15:00', 0], ['Pool', 'Pool', '07-20', 1], ['Hotel', 'Check-out', '12:00', 2]] as [$g, $l, $v, $o]) {
            InfoEntry::create(['hotel_id' => $this->hotelA->id, 'kind' => 'timing', 'group' => $g, 'label' => $l, 'value' => $v, 'sort_order' => $o]);
        }

        $this->get('/timing')->assertInertia(fn ($page) => $page
            ->component('Guest/InfoPage')
            ->has('groups', 2)
            ->where('groups.0.name', 'Hotel')
            ->has('groups.0.items', 2)
            ->where('groups.1.name', 'Pool')
        );
    }

    /* ---------------- media upload ---------------- */

    /** @test */
    public function an_admin_can_upload_a_gallery_photo_to_their_own_room(): void
    {
        Storage::fake('public');
        $room = $this->room($this->hotelA);

        $response = $this->actingAs($this->adminA)->post('/admin/media', [
            'mediable_type' => 'room', 'mediable_id' => $room->id, 'collection' => 'gallery',
            'files' => [UploadedFile::fake()->image('a.jpg'), UploadedFile::fake()->image('b.png')],
        ]);

        $response->assertCreated()->assertJsonCount(2, 'items');
        $this->assertSame(2, Media::where('mediable_id', $room->id)->where('hotel_id', $this->hotelA->id)->count());
        Storage::disk('public')->assertExists(Media::first()->path);
    }

    /** @test */
    public function uploading_a_new_cover_replaces_the_old_one_and_deletes_its_file(): void
    {
        Storage::fake('public');
        $room = $this->room($this->hotelA);
        $post = fn () => $this->actingAs($this->adminA)->post('/admin/media', [
            'mediable_type' => 'room', 'mediable_id' => $room->id, 'collection' => 'cover',
            'files' => [UploadedFile::fake()->image('c.jpg')],
        ])->assertCreated();

        $post();
        $oldPath = Media::first()->path;
        $post();

        $this->assertSame(1, Media::where('collection', 'cover')->count());
        Storage::disk('public')->assertMissing($oldPath);
    }

    /** @test */
    public function non_images_are_rejected_and_nothing_is_stored(): void
    {
        Storage::fake('public');
        $room = $this->room($this->hotelA);

        $this->actingAs($this->adminA)->postJson('/admin/media', [
            'mediable_type' => 'room', 'mediable_id' => $room->id, 'collection' => 'gallery',
            'files' => [UploadedFile::fake()->create('evil.pdf', 10, 'application/pdf')],
        ])->assertStatus(422);

        $this->assertSame(0, Media::count());
    }

    /** @test */
    public function an_admin_cannot_upload_to_or_delete_media_of_another_hotel(): void
    {
        Storage::fake('public');
        $theirRoom = $this->room($this->hotelB, 'theirs');
        $theirMedia = Media::create([
            'hotel_id' => $this->hotelB->id, 'disk' => 'public', 'path' => 'x/theirs.jpg',
            'mediable_type' => Room::class, 'mediable_id' => $theirRoom->id, 'collection' => 'gallery',
        ]);

        $this->actingAs($this->adminA)->postJson('/admin/media', [
            'mediable_type' => 'room', 'mediable_id' => $theirRoom->id, 'collection' => 'gallery',
            'files' => [UploadedFile::fake()->image('a.jpg')],
        ])->assertNotFound();

        $this->actingAs($this->adminA)->deleteJson("/admin/media/{$theirMedia->id}")->assertNotFound();
        $this->assertNotNull($theirMedia->fresh());
    }

    /** @test */
    public function an_unknown_mediable_type_is_rejected(): void
    {
        $this->actingAs($this->adminA)->postJson('/admin/media', [
            'mediable_type' => 'App\\Models\\User', 'mediable_id' => $this->adminA->id, 'collection' => 'gallery',
            'files' => [UploadedFile::fake()->image('a.jpg')],
        ])->assertStatus(422);
    }

    /** @test */
    public function deleting_media_removes_the_row_and_the_file(): void
    {
        Storage::fake('public');
        $room = $this->room($this->hotelA);
        $this->actingAs($this->adminA)->post('/admin/media', [
            'mediable_type' => 'room', 'mediable_id' => $room->id, 'collection' => 'gallery',
            'files' => [UploadedFile::fake()->image('a.jpg')],
        ]);
        $media = Media::first();

        $this->actingAs($this->adminA)->deleteJson("/admin/media/{$media->id}")->assertNoContent();

        $this->assertNull(Media::find($media->id));
        Storage::disk('public')->assertMissing($media->path);
    }

    /** @test */
    public function reorder_updates_sort_order_and_rejects_mixed_owners(): void
    {
        $room = $this->room($this->hotelA);
        $other = $this->room($this->hotelA, 'other');
        $mk = fn ($owner, $n) => Media::create(['hotel_id' => $this->hotelA->id, 'disk' => 'public', 'path' => "p/$n.jpg", 'mediable_type' => Room::class, 'mediable_id' => $owner->id, 'collection' => 'gallery', 'sort_order' => $n]);
        [$a, $b, $c] = [$mk($room, 0), $mk($room, 1), $mk($room, 2)];
        $x = $mk($other, 0);

        $this->actingAs($this->adminA)->putJson('/admin/media/reorder', ['ids' => [$c->id, $a->id, $b->id]])->assertOk();
        $this->assertSame([0, 1, 2], [$c->fresh()->sort_order, $a->fresh()->sort_order, $b->fresh()->sort_order]);

        $this->actingAs($this->adminA)->putJson('/admin/media/reorder', ['ids' => [$a->id, $x->id]])->assertStatus(422);
    }

    /* ---------------- guest rooms ---------------- */

    /** @test */
    public function guest_rooms_only_list_and_open_published_rooms(): void
    {
        $this->room($this->hotelA, 'shown', 'published');
        $this->room($this->hotelA, 'hidden', 'draft');

        $this->get('/rooms')->assertInertia(fn ($page) => $page->component('Guest/Rooms/Index')->has('rooms', 1)->where('rooms.0.slug', 'shown'));
        $this->get('/rooms/shown')->assertOk();
        $this->get('/rooms/hidden')->assertNotFound();
    }

    /** @test */
    public function meeting_rooms_page_lists_only_published_meeting_facilities(): void
    {
        foreach ([['m1', 'meeting', 'published'], ['p1', 'pool', 'published'], ['m2', 'meeting', 'draft']] as [$slug, $cat, $status]) {
            \App\Models\Facility::create(['hotel_id' => $this->hotelA->id, 'name' => $slug, 'slug' => $slug, 'category' => $cat, 'status' => $status]);
        }

        $this->get('/meeting-rooms')->assertInertia(fn ($page) => $page->component('Guest/Facilities/Index')->has('facilities', 1)->where('facilities.0.slug', 'm1'));
    }

    /* ---------------- video + meeting rooms admin ---------------- */

    /** @test */
    public function a_video_can_be_added_to_a_gallery_and_is_reported_as_type_video(): void
    {
        Storage::fake('public');
        $room = $this->room($this->hotelA);

        $this->actingAs($this->adminA)->post('/admin/media', [
            'mediable_type' => 'room', 'mediable_id' => $room->id, 'collection' => 'gallery',
            'files' => [UploadedFile::fake()->create('clip.mp4', 500, 'video/mp4')],
        ])->assertCreated()->assertJsonPath('items.0.type', 'video');
    }

    /** @test */
    public function a_video_is_rejected_as_a_cover_image(): void
    {
        Storage::fake('public');
        $room = $this->room($this->hotelA);

        $this->actingAs($this->adminA)->postJson('/admin/media', [
            'mediable_type' => 'room', 'mediable_id' => $room->id, 'collection' => 'cover',
            'files' => [UploadedFile::fake()->create('clip.mp4', 500, 'video/mp4')],
        ])->assertStatus(422)->assertJsonValidationErrors('files');

        $this->assertSame(0, Media::count());
    }

    /** @test */
    public function media_type_falls_back_to_the_file_extension_when_no_mime_type_is_stored(): void
    {
        $m = new Media(['path' => 'https://cdn.example.com/tour.MP4?x=1']);
        $this->assertTrue($m->isVideo());
        $this->assertFalse((new Media(['path' => 'https://cdn.example.com/a.jpg']))->isVideo());
    }

    /** @test */
    public function room_detail_slides_put_the_cover_first_then_the_gallery(): void
    {
        $room = $this->room($this->hotelA, 'suite');
        $mk = fn ($coll, $order, $path) => Media::create(['hotel_id' => $this->hotelA->id, 'disk' => 'public', 'path' => $path, 'mediable_type' => Room::class, 'mediable_id' => $room->id, 'collection' => $coll, 'sort_order' => $order]);
        $mk('gallery', 0, 'https://x.test/g1.jpg');
        $mk('cover', 0, 'https://x.test/cover.jpg');
        $mk('gallery', 1, 'https://x.test/g2.mp4');

        $this->get('/rooms/suite')->assertInertia(fn ($page) => $page
            ->component('Guest/Rooms/Show')
            ->has('room.slides', 3)
            ->where('room.slides.0.url', 'https://x.test/cover.jpg')
            ->where('room.slides.2.type', 'video')
        );
    }

    /** @test */
    public function admin_facility_list_can_be_filtered_to_meeting_rooms(): void
    {
        foreach ([['m1', 'meeting'], ['p1', 'pool']] as [$slug, $cat]) {
            \App\Models\Facility::create(['hotel_id' => $this->hotelA->id, 'name' => $slug, 'slug' => $slug, 'category' => $cat, 'status' => 'published']);
        }

        $this->actingAs($this->adminA)->get('/admin/facilities?category=meeting')->assertInertia(fn ($page) => $page
            ->component('Admin/Facilities/Index')
            ->where('category', 'meeting')
            ->has('facilities.data', 1)
            ->where('facilities.data.0.slug', 'm1')
        );

        $this->actingAs($this->adminA)->get('/admin/facilities/create?category=meeting')
            ->assertInertia(fn ($page) => $page->where('default_category', 'meeting'));
    }
}
