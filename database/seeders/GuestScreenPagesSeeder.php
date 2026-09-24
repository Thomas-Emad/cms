<?php

namespace Database\Seeders;

use App\Actions\Pages\CreatePageAction;
use App\Actions\Pages\PublishPageAction;
use App\Actions\Pages\SaveDraftAction;
use App\Models\Hotel;
use App\Models\Media;
use App\Models\Page;
use Illuminate\Database\Seeder;
use Illuminate\Validation\ValidationException;

/**
 * Turns the home page's first screen into a story slideshow and creates the
 * placeholder Map page.
 *
 * Safe to run more than once: a page whose slug already exists is left
 * alone (so admin edits are never overwritten), and the home page is only
 * changed if its first section is still a plain `hero`.
 *
 *   php artisan db:seed --class="Database\Seeders\GuestScreenPagesSeeder"
 */
class GuestScreenPagesSeeder extends Seeder
{
    public function run(): void
    {
        $hotel = Hotel::where('slug', 'hilton-grand-horizon')->first() ?? Hotel::orderBy('id')->firstOrFail();

        $mediaIds = Media::query()
            ->where('hotel_id', $hotel->id)
            ->orderBy('id')
            ->limit(12)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $this->upgradeHomeToStorySlideshow($hotel, $mediaIds);

        foreach ($this->pages() as $slug => $definition) {
            $this->createPageIfMissing($hotel, $slug, $definition['name'], $definition['sections']);
        }
    }

    /** @param int[] $mediaIds */
    protected function upgradeHomeToStorySlideshow(Hotel $hotel, array $mediaIds): void
    {
        $home = Page::withoutGlobalScopes()
            ->where('hotel_id', $hotel->id)
            ->where('is_home', true)
            ->first();

        if ($home === null) {
            $this->command?->warn('No home page found - skipped story slideshow. Run PageSeeder first, or add the "Story Slideshow" section in the Builder.');

            return;
        }

        if ($mediaIds === []) {
            $this->command?->warn('No media found for this hotel - skipped story slideshow. Run DemoContentSeeder, or add one in the Builder.');

            return;
        }

        $draft = $home->draftVersion()->first();
        $sections = $draft?->sections['sections'] ?? [];

        if (($sections[0]['type'] ?? null) !== 'hero') {
            $this->command?->info('Home page does not start with a hero section - left unchanged.');

            return;
        }

        $hero = $sections[0]['props'] ?? [];

        $sections[0] = [
            'id' => 'story-home',
            'type' => 'story-slideshow',
            'props' => [
                'title' => $hero['title'] ?? 'Welcome',
                'subtitle' => $hero['subtitle'] ?? '',
                'media_ids' => array_slice($mediaIds, 0, 6),
                'interval_seconds' => 6,
            ],
            'settings' => [],
        ];

        try {
            app(SaveDraftAction::class)->execute($home, $sections);
        } catch (ValidationException $e) {
            $this->command?->warn('Home page has content that no longer validates, so it was left unchanged: '.json_encode($e->errors()));

            return;
        }

        app(PublishPageAction::class)->execute($home->fresh());
        $this->command?->info('Home page: first screen is now a story slideshow (published).');
    }

    /**
     * No Page Builder pages are needed any more: Timing, Short Calls, Rooms & Suites, Gallery,
     * Meeting Rooms and the Map are all real admin-managed features now (see the admin sidebar).
     *
     * @return array<string, array{name: string, sections: array}>
     */
    protected function pages(): array
    {
        return [];
    }

    protected function createPageIfMissing(Hotel $hotel, string $slug, string $name, array $sections): void
    {
        $exists = Page::withoutGlobalScopes()
            ->where('hotel_id', $hotel->id)
            ->where('slug', $slug)
            ->exists();

        if ($exists) {
            $this->command?->info("/pages/{$slug} already exists - left unchanged.");

            return;
        }

        $page = app(CreatePageAction::class)->execute(
            $hotel,
            ['name' => $name, 'slug' => $slug, 'is_home' => false],
            $sections
        );

        app(PublishPageAction::class)->execute($page->fresh());
        $this->command?->info("Created and published /pages/{$slug}.");
    }
}
