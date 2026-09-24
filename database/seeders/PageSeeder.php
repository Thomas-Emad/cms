<?php

namespace Database\Seeders;

use App\Actions\Pages\CreatePageAction;
use App\Actions\Pages\PublishPageAction;
use App\Models\Hotel;
use App\Models\Media;
use App\Models\Page;
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
        if (Page::where('hotel_id', $hotel->id)->where('is_home', true)->exists()) {
            $this->command?->info('Homepage already exists, skipping.');

            return;
        }

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
                    'eyebrow' => 'Luxury Hospitality on the Nile',
                    'title' => 'Welcome to Smarttel Hotel Cairo',
                    'subtitle' => 'Experience timeless Egyptian hospitality on the banks of the River Nile.',
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
                    'description' => 'Everything you need for the perfect Cairo stay.',
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
                    'title' => 'Dining at Smarttel Hotel',
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
                    'heading' => 'Ready to book your stay on the Nile?',
                    'subheading' => 'Our concierge team is here to help you plan the perfect Cairo experience.',
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
        if (Page::where('hotel_id', $hotel->id)->where('slug', 'about')->exists()) {
            $this->command?->info('About page already exists, skipping.');

            return;
        }

        $sections = [
            [
                'id' => 'text-about',
                'type' => 'text',
                'props' => [
                    'heading' => 'About Smarttel Hotel Cairo',
                    'body' => "Smarttel Hotel Cairo has welcomed guests to the shores of the Nile for decades, blending authentic Egyptian hospitality with world-class luxury.\n\nFrom the legendary Nile Grill and the rooftop Nile infinity pool to our Egyptian-inspired spa and state-of-the-art meeting facilities, every detail is designed around your stay.",
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
                    'description' => 'Curated Egyptian moments, made for you.',
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
