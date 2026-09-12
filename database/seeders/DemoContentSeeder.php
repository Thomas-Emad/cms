<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\Experience;
use App\Models\Facility;
use App\Models\Hotel;
use App\Models\Media;
use App\Models\Menu;
use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Models\Offer;
use App\Models\Restaurant;
use App\Models\Service;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;

/**
 * Realistic Grand Horizon demo content. Run after HotelSeeder:
 *   php artisan db:seed --class=Database\\Seeders\\DemoContentSeeder
 *
 * Images use stable Unsplash source URLs directly (Media::getUrlAttribute
 * passes external URLs through as-is) rather than downloading and storing
 * real files - appropriate for demo data, not how real uploads will work
 * once the admin media library exists.
 */
class DemoContentSeeder extends Seeder
{
    protected Hotel $hotel;

    public function run(): void
    {
        $this->hotel = Hotel::where('slug', 'grand-horizon')->firstOrFail();

        $this->seedFacilities();
        $this->seedRestaurants();
        $this->seedServices();
        $this->seedEvents();
        $this->seedOffers();
        $this->seedExperiences();
    }

    protected function attachCover(Model $model, string $url, ?string $alt = null): void
    {
        Media::create([
            'hotel_id' => $this->hotel->id,
            'disk' => 'external',
            'path' => $url,
            'mediable_type' => get_class($model),
            'mediable_id' => $model->id,
            'collection' => 'cover',
            'alt_text' => $alt,
            'sort_order' => 0,
        ]);
    }

    protected function seedFacilities(): void
    {
        $facilities = [
            [
                'name' => 'Serenity Spa', 'slug' => 'serenity-spa', 'category' => 'wellness',
                'short_description' => 'A tranquil escape with signature massages and thermal suites.',
                'description' => "Serenity Spa offers a full menu of massages, facials, and thermal experiences designed to help you unwind. Our therapists blend Mediterranean techniques with modern wellness science.",
                'building' => 'Main Building', 'floor' => '2', 'wing' => 'West Wing',
                'amenities' => ['Sauna', 'Steam Room', 'Hot Tub', 'Relaxation Lounge'],
                'opening_hours' => ['mon_fri' => '09:00-20:00', 'sat_sun' => '09:00-21:00'],
                'phone' => '+1 555 010 2031', 'featured' => true,
                'image' => 'https://images.unsplash.com/photo-1544161515-4ab6ce6db874?w=800',
            ],
            [
                'name' => 'Fitness Center', 'slug' => 'fitness-center', 'category' => 'fitness',
                'short_description' => '24-hour gym with ocean views and personal training.',
                'description' => 'Fully equipped with cardio machines, free weights, and a dedicated studio for group classes. Personal trainers available by appointment.',
                'building' => 'Main Building', 'floor' => '1', 'wing' => 'East Wing',
                'amenities' => ['Free Weights', 'Cardio Machines', 'Yoga Studio', 'Personal Training'],
                'opening_hours' => ['daily' => '00:00-23:59'], 'featured' => false,
                'image' => 'https://images.unsplash.com/photo-1571902943202-507ec2618e8f?w=800',
            ],
            [
                'name' => 'Infinity Pool', 'slug' => 'infinity-pool', 'category' => 'pool',
                'short_description' => 'Rooftop infinity pool overlooking the bay.',
                'description' => 'Our signature infinity pool blends into the horizon, with a swim-up bar and private cabanas available for reservation.',
                'building' => 'Rooftop', 'floor' => 'Roof', 'wing' => null,
                'amenities' => ['Swim-up Bar', 'Private Cabanas', 'Towel Service'],
                'opening_hours' => ['daily' => '07:00-19:00'], 'featured' => true,
                'image' => 'https://images.unsplash.com/photo-1571003123894-1f0594d2b5d9?w=800',
            ],
            [
                'name' => 'Kids Club', 'slug' => 'kids-club', 'category' => 'kids',
                'short_description' => 'Supervised activities and play areas for ages 4-12.',
                'description' => 'A safe, colorful space where children enjoy supervised games, crafts, and daily themed activities while parents relax.',
                'building' => 'Garden Pavilion', 'floor' => '1', 'wing' => null,
                'amenities' => ['Supervised Play', 'Arts & Crafts', 'Outdoor Play Area'],
                'opening_hours' => ['daily' => '09:00-18:00'], 'featured' => false,
                'image' => 'https://images.unsplash.com/photo-1587616211892-b8a3bff0c33c?w=800',
            ],
            [
                'name' => 'Business Center', 'slug' => 'business-center', 'category' => 'business',
                'short_description' => 'Meeting rooms and workstations for the traveling professional.',
                'description' => 'Equipped with high-speed internet, printing services, and bookable meeting rooms for up to 12 people.',
                'building' => 'Main Building', 'floor' => '3', 'wing' => 'East Wing',
                'amenities' => ['High-Speed WiFi', 'Printing', 'Video Conferencing'],
                'opening_hours' => ['mon_fri' => '07:00-22:00'], 'featured' => false,
                'image' => 'https://images.unsplash.com/photo-1497215842964-222b430dc094?w=800',
            ],
            [
                'name' => 'Beach Club', 'slug' => 'beach-club', 'category' => 'beach',
                'short_description' => 'Private beach access with loungers and a beachfront bar.',
                'description' => 'Exclusive beachfront access with complimentary loungers, umbrellas, and a full-service bar steps from the sand.',
                'building' => 'Beachfront', 'floor' => 'Ground', 'wing' => null,
                'amenities' => ['Private Loungers', 'Beach Bar', 'Water Sports Rental'],
                'opening_hours' => ['daily' => '08:00-18:00'], 'featured' => true,
                'image' => 'https://images.unsplash.com/photo-1519046904884-53103b34b206?w=800',
            ],
        ];

        foreach ($facilities as $i => $data) {
            $image = $data['image'];
            unset($data['image']);

            $facility = Facility::create(array_merge($data, [
                'hotel_id' => $this->hotel->id,
                'status' => 'published',
                'sort_order' => $i,
            ]));

            $this->attachCover($facility, $image, $facility->name);
        }
    }

    protected function seedRestaurants(): void
    {
        $azure = Restaurant::create([
            'hotel_id' => $this->hotel->id,
            'name' => 'Azure Restaurant', 'slug' => 'azure-restaurant',
            'description' => 'Fine dining with panoramic sea views, blending Mediterranean and contemporary cuisine.',
            'cuisine' => 'Mediterranean', 'location' => 'Ground Floor, Sea View Wing', 'floor' => '1',
            'opening_hours' => ['breakfast' => '07:00-10:30', 'dinner' => '18:30-22:30'],
            'dress_code' => 'Smart Casual', 'phone' => '+1 555 010 2040',
            'status' => 'published', 'featured' => true, 'sort_order' => 0,
        ]);
        $this->attachCover($azure, 'https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?w=800', 'Azure Restaurant');

        $skyLounge = Restaurant::create([
            'hotel_id' => $this->hotel->id,
            'name' => 'Sky Lounge', 'slug' => 'sky-lounge',
            'description' => 'Rooftop cocktail bar and lounge with live DJ sets on weekends.',
            'cuisine' => 'Small Plates & Cocktails', 'location' => 'Rooftop', 'floor' => 'Roof',
            'opening_hours' => ['daily' => '17:00-01:00'],
            'dress_code' => 'Smart Casual', 'phone' => '+1 555 010 2041',
            'status' => 'published', 'featured' => false, 'sort_order' => 1,
        ]);
        $this->attachCover($skyLounge, 'https://images.unsplash.com/photo-1470337458703-46ad1756a187?w=800', 'Sky Lounge');

        $this->seedMenu($azure, [
            'Breakfast' => [
                ['Grand Horizon Breakfast', 'Eggs any style, pastries, seasonal fruit, fresh juice.', 22.00, ['vegetarian']],
                ['Avocado Toast', 'Sourdough, smashed avocado, poached egg, chili flakes.', 16.00, ['vegetarian']],
            ],
            'Main Courses' => [
                ['Grilled Sea Bass', 'Lemon herb butter, roasted vegetables.', 38.00, ['gluten-free']],
                ['Lamb Tagine', 'Slow-cooked lamb, apricots, couscous.', 34.00, []],
            ],
            'Desserts' => [
                ['Baklava Trio', 'Pistachio, walnut, and almond baklava.', 12.00, ['vegetarian']],
            ],
        ]);

        $this->seedMenu($skyLounge, [
            'Drinks' => [
                ['Horizon Sunset', 'Signature gin cocktail with citrus and elderflower.', 15.00, []],
                ['Mediterranean Mule', 'Vodka, ginger beer, fresh mint.', 14.00, []],
            ],
            'Starters' => [
                ['Mezze Platter', 'Hummus, baba ganoush, olives, flatbread.', 18.00, ['vegetarian']],
            ],
        ]);
    }

    protected function seedMenu(Restaurant $restaurant, array $categories): void
    {
        $menu = Menu::create(['restaurant_id' => $restaurant->id, 'name' => 'Main Menu', 'is_active' => true]);

        foreach ($categories as $categoryName => $items) {
            $category = MenuCategory::create(['menu_id' => $menu->id, 'name' => $categoryName, 'sort_order' => 0]);

            foreach ($items as $i => [$name, $description, $price, $dietary]) {
                MenuItem::create([
                    'menu_category_id' => $category->id,
                    'name' => $name, 'description' => $description, 'price' => $price,
                    'dietary_info' => $dietary, 'is_available' => true, 'sort_order' => $i,
                ]);
            }
        }
    }

    protected function seedServices(): void
    {
        $services = [
            ['Room Service', 'room-service', '24-hour in-room dining from the Azure Restaurant menu.', 'utensils', true, null],
            ['Laundry', 'laundry', 'Same-day laundry and dry cleaning service.', 'shirt', true, 12.00],
            ['Housekeeping', 'housekeeping', 'Daily housekeeping, available on request for evening turndown.', 'sparkles', true, null],
            ['Concierge', 'concierge', 'Reservations, recommendations, and local experiences arranged for you.', 'bell', false, null],
            ['Airport Transfer', 'airport-transfer', 'Private car service to and from the airport.', 'car', true, 45.00],
        ];

        foreach ($services as $i => [$name, $slug, $desc, $icon, $requestEnabled, $price]) {
            Service::create([
                'hotel_id' => $this->hotel->id,
                'name' => $name, 'slug' => $slug, 'description' => $desc, 'icon' => $icon,
                'request_enabled' => $requestEnabled, 'price' => $price,
                'status' => 'published', 'sort_order' => $i,
            ]);
        }
    }

    protected function seedEvents(): void
    {
        $events = [
            ['Live Music Night', 'live-music-night', 'Acoustic sets on the Sky Lounge terrace.', 'Sky Lounge', now()->addDays(2), 'https://images.unsplash.com/photo-1493225457124-a3eb161ffa5f?w=800'],
            ['Mediterranean Night', 'mediterranean-night', 'A themed buffet dinner celebrating coastal flavors.', 'Azure Restaurant', now()->addDays(5), 'https://images.unsplash.com/photo-1414235077428-338989a2e8c0?w=800'],
            ['Pool Party', 'pool-party', 'DJ sets and cocktails at the Infinity Pool.', 'Infinity Pool', now()->addDays(9), 'https://images.unsplash.com/photo-1571003123894-1f0594d2b5d9?w=800'],
            ['Sunset Yoga', 'sunset-yoga', 'Guided yoga session as the sun sets over the bay.', 'Beach Club', now()->addDays(1), 'https://images.unsplash.com/photo-1544367567-0f2fcb009e0b?w=800'],
        ];

        foreach ($events as [$title, $slug, $desc, $location, $date, $image]) {
            $event = Event::create([
                'hotel_id' => $this->hotel->id,
                'title' => $title, 'slug' => $slug, 'description' => $desc,
                'location' => $location, 'start_date' => $date, 'start_time' => '19:00:00',
                'booking_required' => false, 'status' => 'published',
            ]);
            $this->attachCover($event, $image, $title);
        }
    }

    protected function seedOffers(): void
    {
        $offers = [
            ['Spa Package', 'spa-package', '90-minute massage plus full thermal suite access.', 149.00, 15, true, 'https://images.unsplash.com/photo-1540555700478-4be289fbecef?w=800'],
            ['Dinner for Two', 'dinner-for-two', 'Three-course dinner at Azure Restaurant with a bottle of wine.', 120.00, 10, false, 'https://images.unsplash.com/photo-1414235077428-338989a2e8c0?w=800'],
            ['Weekend Escape', 'weekend-escape', 'Two-night stay with breakfast and late checkout.', 480.00, 20, true, 'https://images.unsplash.com/photo-1566073771259-6a8506099945?w=800'],
            ['Family Package', 'family-package', 'Family room, kids club access, and daily breakfast.', 620.00, 12, false, 'https://images.unsplash.com/photo-1602002418082-a4443e081dd1?w=800'],
        ];

        foreach ($offers as [$title, $slug, $desc, $price, $discount, $featured, $image]) {
            $offer = Offer::create([
                'hotel_id' => $this->hotel->id,
                'title' => $title, 'slug' => $slug, 'description' => $desc,
                'price' => $price, 'discount' => $discount, 'featured' => $featured,
                'valid_from' => now(), 'valid_until' => now()->addMonths(2),
                'status' => 'published',
            ]);
            $this->attachCover($offer, $image, $title);
        }
    }

    protected function seedExperiences(): void
    {
        $experiences = [
            ['Yoga Session', 'yoga-session', 'wellness', 'Morning yoga overlooking the sea.', '60 minutes', 25.00, true, 'https://images.unsplash.com/photo-1544367567-0f2fcb009e0b?w=800'],
            ['Cooking Class', 'cooking-class', 'culinary', 'Learn Mediterranean recipes with our executive chef.', '2 hours', 65.00, true, 'https://images.unsplash.com/photo-1556910103-1c02745aae4d?w=800'],
            ['Sunset Dinner', 'sunset-dinner', 'culinary', 'A private beachfront dinner as the sun sets.', '2 hours', 180.00, true, 'https://images.unsplash.com/photo-1414235077428-338989a2e8c0?w=800'],
            ['Kids Activities', 'kids-activities-experience', 'family', 'A full day of themed games and crafts for children.', 'Full day', 0.00, false, 'https://images.unsplash.com/photo-1587616211892-b8a3bff0c33c?w=800'],
        ];

        foreach ($experiences as $i => [$title, $slug, $category, $desc, $duration, $price, $featured, $image]) {
            $experience = Experience::create([
                'hotel_id' => $this->hotel->id,
                'title' => $title, 'slug' => $slug, 'category' => $category,
                'description' => $desc, 'duration' => $duration, 'price' => $price,
                'featured' => $featured, 'status' => 'published', 'sort_order' => $i,
            ]);
            $this->attachCover($experience, $image, $title);
        }
    }
}
