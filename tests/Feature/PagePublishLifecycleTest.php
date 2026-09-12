<?php

namespace Tests\Feature;

use App\Actions\Pages\CreatePageAction;
use App\Actions\Pages\PublishPageAction;
use App\Actions\Pages\SaveDraftAction;
use App\Models\Hotel;
use App\Models\Page;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PagePublishLifecycleTest extends TestCase
{
    use RefreshDatabase;

    protected Hotel $hotel;

    protected function setUp(): void
    {
        parent::setUp();

        $this->hotel = Hotel::create([
            'name' => 'Test Hotel', 'slug' => 'test-hotel-' . uniqid(), 'status' => 'active',
        ]);
    }

    /** @test */
    public function first_publish_creates_a_published_version_and_sets_the_pointer(): void
    {
        $page = app(CreatePageAction::class)->execute($this->hotel, [
            'name' => 'Homepage', 'slug' => '', 'is_home' => true,
        ], [
            ['id' => 'hero-1', 'type' => 'hero', 'props' => ['title' => 'Welcome'], 'settings' => []],
        ]);

        $this->assertNull($page->published_version_id);
        $this->assertNotNull($page->draft_version_id);

        $published = app(PublishPageAction::class)->execute($page->fresh());

        $page->refresh();

        $this->assertEquals($published->id, $page->published_version_id);
        $this->assertEquals('published', $page->status);
        $this->assertEquals('published', $published->state);
        $this->assertNotNull($published->published_at);
        $this->assertEquals(
            'Welcome',
            $published->sections['sections'][0]['props']['title']
        );
    }

    /** @test */
    public function editing_the_draft_after_publish_does_not_affect_the_published_version(): void
    {
        $page = app(CreatePageAction::class)->execute($this->hotel, [
            'name' => 'Homepage', 'slug' => '', 'is_home' => true,
        ], [
            ['id' => 'hero-1', 'type' => 'hero', 'props' => ['title' => 'Version A'], 'settings' => []],
        ]);

        $publishedA = app(PublishPageAction::class)->execute($page->fresh());
        $draftVersionId = $page->fresh()->draft_version_id;

        // Edit the draft after publishing.
        app(SaveDraftAction::class)->execute($page->fresh(), [
            ['id' => 'hero-1', 'type' => 'hero', 'props' => ['title' => 'Version B (unpublished)'], 'settings' => []],
        ]);

        $page->refresh();

        // The draft ROW ITSELF never changed identity - same design
        // requirement as "the builder must always edit the current draft".
        $this->assertEquals($draftVersionId, $page->draft_version_id);

        // But the published version, fetched fresh from the DB, must be
        // completely unaffected by the draft edit.
        $publishedA->refresh();
        $this->assertEquals('Version A', $publishedA->sections['sections'][0]['props']['title']);

        // And the pointer hasn't moved.
        $this->assertEquals($publishedA->id, $page->published_version_id);
    }

    /** @test */
    public function second_publish_creates_a_new_version_row_and_moves_the_pointer(): void
    {
        $page = app(CreatePageAction::class)->execute($this->hotel, [
            'name' => 'Homepage', 'slug' => '', 'is_home' => true,
        ], [
            ['id' => 'hero-1', 'type' => 'hero', 'props' => ['title' => 'Version A'], 'settings' => []],
        ]);

        $publishedA = app(PublishPageAction::class)->execute($page->fresh());

        app(SaveDraftAction::class)->execute($page->fresh(), [
            ['id' => 'hero-1', 'type' => 'hero', 'props' => ['title' => 'Version B'], 'settings' => []],
        ]);

        $publishedB = app(PublishPageAction::class)->execute($page->fresh());

        // A genuinely new row, not a mutation of the first.
        $this->assertNotEquals($publishedA->id, $publishedB->id);

        $page->refresh();
        $this->assertEquals($publishedB->id, $page->published_version_id);

        // publishedA still exists, unmodified, as revision history.
        $publishedA->refresh();
        $this->assertEquals('Version A', $publishedA->sections['sections'][0]['props']['title']);
        $this->assertEquals('Version B', $publishedB->sections['sections'][0]['props']['title']);

        // Two published rows now exist for this page - the revision log.
        $this->assertEquals(
            2,
            $page->versions()->where('state', 'published')->count()
        );
    }

    /** @test */
    public function guest_always_sees_the_latest_published_version(): void
    {
        $page = app(CreatePageAction::class)->execute($this->hotel, [
            'name' => 'Homepage', 'slug' => '', 'is_home' => true,
        ], [
            ['id' => 'hero-1', 'type' => 'hero', 'props' => ['title' => 'Version A'], 'settings' => []],
        ]);

        app(PublishPageAction::class)->execute($page->fresh());

        app(SaveDraftAction::class)->execute($page->fresh(), [
            ['id' => 'hero-1', 'type' => 'hero', 'props' => ['title' => 'Version B'], 'settings' => []],
        ]);

        app(PublishPageAction::class)->execute($page->fresh());

        $response = $this->get('/');

        $response->assertOk();
        $response->assertInertia(fn ($assert) => $assert
            ->component('Guest/PageView')
            ->where('sections.0.props.title', 'Version B')
        );
    }

    /** @test */
    public function draft_changes_never_affect_the_live_page_before_publish(): void
    {
        $page = app(CreatePageAction::class)->execute($this->hotel, [
            'name' => 'Homepage', 'slug' => '', 'is_home' => true,
        ], [
            ['id' => 'hero-1', 'type' => 'hero', 'props' => ['title' => 'Published Title'], 'settings' => []],
        ]);

        app(PublishPageAction::class)->execute($page->fresh());

        // Edit draft, but do NOT publish.
        app(SaveDraftAction::class)->execute($page->fresh(), [
            ['id' => 'hero-1', 'type' => 'hero', 'props' => ['title' => 'Unpublished Draft Title'], 'settings' => []],
        ]);

        $response = $this->get('/');

        $response->assertOk();
        $response->assertInertia(fn ($assert) => $assert
            ->where('sections.0.props.title', 'Published Title')
        );
    }

    /** @test */
    public function a_page_with_no_published_version_returns_404_to_guests(): void
    {
        app(CreatePageAction::class)->execute($this->hotel, [
            'name' => 'Homepage', 'slug' => '', 'is_home' => true,
        ], [
            ['id' => 'hero-1', 'type' => 'hero', 'props' => ['title' => 'Draft Only'], 'settings' => []],
        ]);

        // Never published.
        $this->get('/')->assertNotFound();
    }
}
