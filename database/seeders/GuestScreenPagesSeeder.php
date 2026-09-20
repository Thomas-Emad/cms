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
 * Creates the pages behind the guest-screen dock buttons and turns the home
 * page's first screen into a story slideshow.
 *
 * Safe to run more than once: a page whose slug already exists is left
 * alone (so admin edits are never overwritten), and the home page is only
 * changed if its first section is still a plain `hero`.
 *
 * The rows in Timing / Short Calls / Rooms are SAMPLE content to be
 * replaced by the hotel's real data in the admin Builder.
 *
 *   php artisan db:seed --class="Database\Seeders\GuestScreenPagesSeeder"
 */
class GuestScreenPagesSeeder extends Seeder
{
    public function run(): void
    {
        $hotel = Hotel::where('slug', 'grand-horizon')->first() ?? Hotel::orderBy('id')->firstOrFail();

        $mediaIds = Media::query()
            ->where('hotel_id', $hotel->id)
            ->orderBy('id')
            ->limit(12)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $this->upgradeHomeToStorySlideshow($hotel, $mediaIds);

        foreach ($this->pages($mediaIds) as $slug => $definition) {
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
            $this->command?->warn('Home page has content that no longer validates, so it was left unchanged: ' . json_encode($e->errors()));

            return;
        }

        app(PublishPageAction::class)->execute($home->fresh());
        $this->command?->info('Home page: first screen is now a story slideshow (published).');
    }

    /**
     * @param int[] $mediaIds
     * @return array<string, array{name: string, sections: array}>
     */
    protected function pages(array $mediaIds): array
    {
        $list = fn (array $lines) => implode("\n", $lines);

        return [
            'timing' => [
                'name' => 'Timing',
                'sections' => [[
                    'id' => 'info-timing',
                    'type' => 'info-list',
                    'props' => [
                        'title' => 'Opening Times',
                        'description' => 'Sample times - replace with your own in the Page Builder.',
                        'items_text' => $list([
                            'Dining',
                            'Breakfast | 06:30 - 10:30',
                            'Lunch | 12:00 - 15:00',
                            'Dinner | 18:30 - 23:00',
                            'Leisure',
                            'Pool | 07:00 - 20:00',
                            'Spa | 09:00 - 21:00',
                            'Fitness Centre | 24 hours',
                            'Hotel',
                            'Check-in | from 15:00',
                            'Check-out | until 12:00',
                        ]),
                    ],
                    'settings' => ['padding' => 'large'],
                ]],
            ],

            'map' => [
                'name' => 'Map',
                'sections' => [
                    [
                        'id' => 'text-map',
                        'type' => 'text',
                        'props' => [
                            'heading' => 'Hotel Map',
                            'body' => "Add your floor plan or site map with the Image section below (upload it, then set its media id in the Page Builder).",
                        ],
                        'settings' => ['padding' => 'large'],
                    ],
                    [
                        'id' => 'image-map',
                        'type' => 'image',
                        'props' => ['media_id' => null, 'caption' => '', 'link_url' => ''],
                        'settings' => ['padding' => 'medium'],
                    ],
                ],
            ],

            'short-calls' => [
                'name' => 'Short Calls',
                'sections' => [[
                    'id' => 'info-short-calls',
                    'type' => 'info-list',
                    'props' => [
                        'title' => 'Short Calls',
                        'description' => 'Dial from your room phone. Sample numbers - replace with your own.',
                        'items_text' => $list([
                            'Reception | 0',
                            'Concierge | 100',
                            'Room Service | 101',
                            'Housekeeping | 102',
                            'Spa | 103',
                            'Restaurants | 104',
                            'Security | 199',
                        ]),
                    ],
                    'settings' => ['padding' => 'large'],
                ]],
            ],

            'rooms-suites' => [
                'name' => 'Rooms & Suites',
                'sections' => [[
                    'id' => 'info-rooms',
                    'type' => 'info-list',
                    'props' => [
                        'title' => 'Rooms & Suites',
                        'description' => 'Sample room types - replace with your own in the Page Builder.',
                        'items_text' => $list([
                            'Deluxe Room | 32 m2 - garden view',
                            'Superior Room | 38 m2 - sea view',
                            'Executive Suite | 55 m2 - sea view',
                            'Presidential Suite | 120 m2 - panoramic terrace',
                        ]),
                    ],
                    'settings' => ['padding' => 'large'],
                ]],
            ],

            'gallery' => [
                'name' => 'Gallery',
                'sections' => [[
                    'id' => 'gallery-main',
                    'type' => 'gallery',
                    'props' => ['title' => 'Gallery', 'media_ids' => $mediaIds],
                    'settings' => ['padding' => 'medium', 'layout' => 'grid'],
                ]],
            ],

            'meeting-room' => [
                'name' => 'Meeting Room',
                'sections' => [
                    [
                        'id' => 'text-meeting',
                        'type' => 'text',
                        'props' => [
                            'heading' => 'Meetings & Events',
                            'body' => 'Rooms for business meetings, conferences and private gatherings. Add any facility with the category "meeting" and it appears below automatically.',
                        ],
                        'settings' => ['padding' => 'large'],
                    ],
                    [
                        'id' => 'facility-grid-meeting',
                        'type' => 'facility-grid',
                        'props' => [
                            'title' => 'Our Meeting Rooms',
                            'description' => null,
                            'category' => 'meeting',
                            'featured_only' => false,
                            'limit' => 6,
                            'columns' => 3,
                        ],
                        'settings' => ['background' => 'light', 'padding' => 'medium'],
                    ],
                ],
            ],
        ];
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
