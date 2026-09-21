<?php

namespace Database\Seeders;

use App\Actions\Pages\CreatePageAction;
use App\Actions\Pages\PublishPageAction;
use App\Models\Hotel;
use App\Models\Media;
use Illuminate\Database\Seeder;

/**
 * Creates a realistic Grand Horizon homepage using the ACTUAL Page
 * Builder pipeline (CreatePageAction -> draft PageVersion -> save ->
 * PublishPageAction), not a hand-crafted database row - so what you see
 * on the guest homepage and what you'd see opening this page in
 * /admin/pages/{id}/builder are guaranteed to be in sync with how a real
 * admin session would produce it.
 *
 * Run after HotelSeeder and DemoContentSeeder (this page's facility-grid/
 * restaurant-grid/offers sections reference real seeded content by
 * category/featured flags, not by ID, so it'll render empty grids if the
 * demo content doesn't exist yet - not broken, just nothing to show).
 *
 *   php artisan db:seed --class=Database\\Seeders\\PageSeeder
 */
class PageSeeder extends Seeder
{
    public function run(): void
    {
        $hotel = Hotel::where('slug', 'hilton-grand-horizon')->firstOrFail();

        $this->seedHomepage($hotel);
        $this->seedAboutPage($hotel);
    }

    protected function seedHomepage(Hotel $hotel): void
    {
        $heroMediaId = Media::query()
            ->where('hotel_id', $hotel->id)
            ->where('collection', 'cover')
            ->orderBy('id')
            ->value('id');

        $sections = [
            [
                'id' => 'hero-home',
                'type' => 'hero',
                'props' => [
                    'media_id' => $heroMediaId,
                    'title' => 'Welcome to Grand Horizon',
                    'subtitle' => 'Discover an unforgettable hotel experience on the water\'s edge.',
                    'button_text' => 'Explore Facilities',
                    'button_url' => '/facilities',
                ],
                'settings' => ['background' => 'transparent', 'padding' => 'large'],
            ],
            [
                'id' => 'facility-grid-home',
                'type' => 'facility-grid',
                'props' => [
                    'title' => 'Explore Our Facilities',
                    'description' => 'Everything you need for the perfect stay.',
                    'category' => null,
                    'featured_only' => true,
                    'limit' => 6,
                    'columns' => 3,
                ],
                'settings' => ['background' => 'light', 'padding' => 'medium'],
            ],
            [
                'id' => 'restaurant-grid-home',
                'type' => 'restaurant-grid',
                'props' => [
                    'title' => 'Dining at Grand Horizon',
                    'description' => null,
                    'cuisine' => null,
                    'featured_only' => true,
                    'limit' => 4,
                ],
                'settings' => ['background' => 'transparent', 'padding' => 'medium'],
            ],
            [
                'id' => 'offers-home',
                'type' => 'offers',
                'props' => [
                    'title' => 'Special Offers',
                    'limit' => 4,
                    'active_only' => true,
                    'featured_only' => true,
                ],
                'settings' => ['padding' => 'medium'],
            ],
            [
                'id' => 'events-home',
                'type' => 'events',
                'props' => [
                    'title' => 'Upcoming Events',
                    'limit' => 4,
                    'upcoming_only' => true,
                ],
                'settings' => ['padding' => 'medium'],
            ],
            [
                'id' => 'cta-home',
                'type' => 'cta',
                'props' => [
                    'heading' => 'Ready to book your stay?',
                    'subheading' => 'Our concierge team is here to help you plan the perfect visit.',
                    'button_text' => 'Contact Us',
                    'button_url' => '/facilities',
                ],
                'settings' => ['background' => 'brand', 'padding' => 'large'],
            ],
        ];

        $page = app(CreatePageAction::class)->execute(
            $hotel,
            ['name' => 'Homepage', 'slug' => '', 'is_home' => true],
            $sections
        );

        app(PublishPageAction::class)->execute($page->fresh());

        $this->command?->info("Homepage created and published (page id: {$page->id}).");
    }

    protected function seedAboutPage(Hotel $hotel): void
    {
        $sections = [
            [
                'id' => 'text-about',
                'type' => 'text',
                'props' => [
                    'heading' => 'About Grand Horizon',
                    'body' => "Grand Horizon Hotel has welcomed guests to the bay for over two decades, blending Mediterranean hospitality with modern comfort.\n\nFrom our award-winning restaurants to our tranquil spa, every detail is designed around your stay.",
                ],
                'settings' => ['padding' => 'large'],
            ],
            [
                'id' => 'spacer-about',
                'type' => 'spacer',
                'props' => ['height' => 'medium'],
                'settings' => [],
            ],
            [
                'id' => 'experiences-about',
                'type' => 'experiences',
                'props' => [
                    'title' => 'Experiences',
                    'description' => 'Curated moments, made for you.',
                    'category' => null,
                    'featured_only' => false,
                    'limit' => 4,
                ],
                'settings' => ['background' => 'light', 'padding' => 'medium'],
            ],
        ];

        $page = app(CreatePageAction::class)->execute(
            $hotel,
            ['name' => 'About', 'slug' => 'about', 'is_home' => false],
            $sections
        );

        app(PublishPageAction::class)->execute($page->fresh());

        $this->command?->info("About page created and published (page id: {$page->id}, /pages/about).");
    }
}
