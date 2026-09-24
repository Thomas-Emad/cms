<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\Experience;
use App\Models\Facility;
use App\Models\Hotel;
use App\Models\InfoEntry;
use App\Models\Media;
use App\Models\Menu;
use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Models\Offer;
use App\Models\Restaurant;
use App\Models\Room;
use App\Models\Service;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;

/**
 * Realistic Hilton Grand Horizon demo content. Run after HotelSeeder:
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
        $this->hotel = Hotel::where('slug', 'hilton-grand-horizon')->firstOrFail();

        $this->seedInfoEntries();
        $this->seedRooms();
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

    protected function attachGalleryImage(
        Model $model,
        string $url,
        ?string $alt = null,
        int $sortOrder = 0
    ): void {
        Media::create([
            'hotel_id' => $this->hotel->id,
            'disk' => 'external',
            'path' => $url,
            'mediable_type' => get_class($model),
            'mediable_id' => $model->id,
            'collection' => 'gallery',
            'alt_text' => $alt,
            'sort_order' => $sortOrder,
        ]);
    }

    protected function seedRooms(): void
    {
        $rooms = [
            [
                'name' => 'King Guest Room',
                'slug' => 'king-guest-room',
                'short_description' => 'Modern comfort with a plush king bed and city views.',
                'description' => 'Our King Guest Room blends contemporary Hilton design with a Serta Suite Dreams bed, a spacious work desk, and floor-to-ceiling windows overlooking the skyline.',
                'size_sqm' => 32,
                'max_guests' => 2,
                'bed_type' => '1 King Bed',
                'view' => 'City View',
                'features' => ['Free WiFi', '55" Smart TV', 'Rainfall Shower', 'Nespresso Machine', 'In-room Safe'],
                'featured' => false,
                'image' => 'https://images.unsplash.com/photo-1611892440504-42a792e24d32?w=1000',
            ],
            [
                'name' => 'Double Queen Room',
                'slug' => 'double-queen-room',
                'short_description' => 'Two queen beds, ideal for families and small groups.',
                'description' => 'Spacious and flexible, this room offers two queen beds, a seating area, and all the connectivity families need for a comfortable stay together.',
                'size_sqm' => 36,
                'max_guests' => 4,
                'bed_type' => '2 Queen Beds',
                'view' => 'Garden View',
                'features' => ['Free WiFi', '55" Smart TV', 'Mini Fridge', 'Blackout Curtains', 'Sofa Chair'],
                'featured' => false,
                'image' => 'https://images.unsplash.com/photo-1590490360182-c33d57733427?w=1000',
            ],
            [
                'name' => 'Executive King Suite',
                'slug' => 'executive-king-suite',
                'short_description' => 'A separate living area plus Executive Lounge access.',
                'description' => 'Unwind in a separate living room with a king bedroom, upgraded amenities, and complimentary access to the Executive Lounge for breakfast and evening hors d\'oeuvres.',
                'size_sqm' => 55,
                'max_guests' => 3,
                'bed_type' => '1 King Bed',
                'view' => 'Ocean View',
                'features' => ['Executive Lounge Access', 'Separate Living Room', 'Soaking Tub', 'Bathrobe & Slippers', 'Premium Minibar'],
                'featured' => true,
                'image' => 'https://images.unsplash.com/photo-1590073242678-70ee3fc28f8e?w=1000',
            ],
            [
                'name' => 'Junior Suite',
                'slug' => 'junior-suite',
                'short_description' => 'Open-plan suite with a lounge corner and city skyline views.',
                'description' => 'An open-plan layout with a dedicated lounge corner, walk-in closet, and floor-to-ceiling windows framing the skyline — ideal for extended stays.',
                'size_sqm' => 48,
                'max_guests' => 3,
                'bed_type' => '1 King Bed',
                'view' => 'City Skyline',
                'features' => ['Walk-in Closet', 'Lounge Area', 'Espresso Machine', 'Two Smart TVs', 'Free WiFi'],
                'featured' => false,
                'image' => 'https://images.unsplash.com/photo-1560185893-a55cbc8c57e8?w=1000',
            ],
            [
                'name' => 'Grand Horizon Suite',
                'slug' => 'grand-horizon-suite',
                'short_description' => 'Our top-floor signature suite with a private terrace.',
                'description' => 'The pinnacle of the property: a top-floor suite with a private terrace, dining area for six, and panoramic views of the bay from every room.',
                'size_sqm' => 90,
                'max_guests' => 4,
                'bed_type' => '1 King Bed + Sofa Bed',
                'view' => 'Panoramic Bay View',
                'features' => ['Private Terrace', 'Dining Area for 6', 'Butler Service on Request', 'Jacuzzi Tub', 'Complimentary Airport Transfer'],
                'featured' => true,
                'image' => 'https://images.unsplash.com/photo-1582719508461-905c673771fd?w=1000',
            ],
            [
                'name' => 'Accessible King Room',
                'slug' => 'accessible-king-room',
                'short_description' => 'ADA-compliant room with a roll-in shower and wide doorways.',
                'description' => 'Thoughtfully designed for accessibility, with a roll-in shower, grab bars, wider doorways, and a lowered wardrobe rail, without compromising on comfort.',
                'size_sqm' => 34,
                'max_guests' => 2,
                'bed_type' => '1 King Bed',
                'view' => 'Garden View',
                'features' => ['Roll-in Shower', 'Grab Bars', 'Visual Alarm', 'Free WiFi', 'Lowered Amenities'],
                'featured' => false,
                'image' => 'https://images.unsplash.com/photo-1611892440504-42a792e24d32?w=1000',
            ],
        ];

        foreach ($rooms as $i => $data) {
            $image = $data['image'];
            unset($data['image']);

            $room = Room::create(array_merge($data, [
                'hotel_id' => $this->hotel->id,
                'status' => 'published',
                'sort_order' => $i,
            ]));

            $this->attachCover($room, $image, $room->name);
        }
    }

    protected function seedFacilities(): void
    {
        $facilities = [
            [
                'name' => 'Eforea Spa',
                'slug' => 'eforea-spa',
                'category' => 'wellness',
                'short_description' => 'Hilton\'s signature spa brand, with massages and thermal suites.',
                'description' => 'Eforea Spa offers a full menu of massages, facials, and thermal experiences designed to help you unwind, blending global techniques with modern wellness science.',
                'building' => 'Main Building',
                'floor' => '2',
                'wing' => 'West Wing',
                'pos_x' => 32.5,
                'pos_y' => 18.2,
                'amenities' => [
                    'Sauna',
                    'Steam Room',
                    'Hot Tub',
                    'Relaxation Lounge',
                ],
                'opening_hours' => [
                    'mon_fri' => '09:00-20:00',
                    'sat_sun' => '09:00-21:00',
                ],
                'phone' => '+1 555 010 2031',
                'email' => 'spa@grandhorizon.example',
                'featured' => true,

                'cover' => 'https://images.unsplash.com/photo-1540555700478-4be289fbecef?w=1200',
                'gallery' => [
                    'https://images.unsplash.com/photo-1544161515-4ab6ce6db874?w=1200',
                    'https://images.unsplash.com/photo-1600334089648-b0d9d3028eb2?w=1200',
                    'https://images.unsplash.com/photo-1540555700478-4be289fbecef?w=1200',
                ],
            ],

            [
                'name' => 'LivingWell Fitness Center',
                'slug' => 'livingwell-fitness-center',
                'category' => 'fitness',
                'short_description' => '24-hour gym with Peloton bikes and skyline views.',
                'description' => 'Fully equipped with Peloton bikes, free weights, and a dedicated studio for group classes. Personal trainers available by appointment.',
                'building' => 'Main Building',
                'floor' => '1',
                'wing' => 'East Wing',
                'pos_x' => 61.4,
                'pos_y' => 42.8,
                'amenities' => [
                    'Peloton Bikes',
                    'Free Weights',
                    'Cardio Machines',
                    'Personal Training',
                ],
                'opening_hours' => [
                    'daily' => '00:00-23:59',
                ],
                'phone' => '+1 555 010 2032',
                'email' => 'fitness@grandhorizon.example',
                'featured' => false,

                'cover' => 'https://images.unsplash.com/photo-1534438327276-14e5300c3a48?w=1200',
                'gallery' => [
                    'https://images.unsplash.com/photo-1571902943202-507ec2618e8f?w=1200',
                    'https://images.unsplash.com/photo-1581009146145-b5ef050c2e1e?w=1200',
                    'https://images.unsplash.com/photo-1593079831268-3381b0db4a77?w=1200',
                ],
            ],

            [
                'name' => 'Horizon Rooftop Pool',
                'slug' => 'horizon-rooftop-pool',
                'category' => 'pool',
                'short_description' => 'Rooftop infinity pool overlooking the bay.',
                'description' => 'Our signature infinity pool blends into the horizon, with a swim-up bar and private cabanas available for reservation.',
                'building' => 'Rooftop',
                'floor' => 'Roof',
                'wing' => null,
                'pos_x' => 50.0,
                'pos_y' => 8.5,
                'amenities' => [
                    'Swim-up Bar',
                    'Private Cabanas',
                    'Towel Service',
                ],
                'opening_hours' => [
                    'daily' => '07:00-19:00',
                ],
                'phone' => '+1 555 010 2033',
                'email' => 'pool@grandhorizon.example',
                'featured' => true,

                'cover' => 'https://images.unsplash.com/photo-1571896349842-33c89424de2d?w=1200',
                'gallery' => [
                    'https://images.unsplash.com/photo-1602002418082-a4443e081dd1?w=1200',
                    'https://images.unsplash.com/photo-1572331165267-854da2b10ccc?w=1200',
                    'https://images.unsplash.com/photo-1564501049412-61c2a3083791?w=1200',
                ],
            ],

            [
                'name' => 'Hilton Adventures Kids Club',
                'slug' => 'hilton-adventures-kids-club',
                'category' => 'kids',
                'short_description' => 'Supervised activities and play areas for ages 4-12.',
                'description' => 'A safe, colorful space where children enjoy supervised games, crafts, and daily themed activities while parents relax.',
                'building' => 'Garden Pavilion',
                'floor' => '1',
                'wing' => null,
                'pos_x' => 18.7,
                'pos_y' => 67.2,
                'amenities' => [
                    'Supervised Play',
                    'Arts & Crafts',
                    'Outdoor Play Area',
                ],
                'opening_hours' => [
                    'daily' => '09:00-18:00',
                ],
                'phone' => '+1 555 010 2034',
                'email' => 'kids@grandhorizon.example',
                'featured' => false,

                'cover' => 'https://images.unsplash.com/photo-1560184897-ae75f418493e?w=1200',
                'gallery' => [
                    'https://images.unsplash.com/photo-1587654780291-39c9404d746b?w=1200',
                    'https://images.unsplash.com/photo-1596464716127-f2a82984de30?w=1200',
                    'https://images.unsplash.com/photo-1560185008-b033106af5c3?w=1200',
                ],
            ],

            [
                'name' => 'Executive Business Center',
                'slug' => 'executive-business-center',
                'category' => 'business',
                'short_description' => 'Meeting rooms and workstations for the traveling professional.',
                'description' => 'Equipped with high-speed internet, printing services, and bookable meeting rooms for up to 12 people.',
                'building' => 'Main Building',
                'floor' => '3',
                'wing' => 'East Wing',
                'pos_x' => 72.1,
                'pos_y' => 34.6,
                'amenities' => [
                    'High-Speed WiFi',
                    'Printing',
                    'Video Conferencing',
                ],
                'opening_hours' => [
                    'mon_fri' => '07:00-22:00',
                ],
                'phone' => '+1 555 010 2035',
                'email' => 'business@grandhorizon.example',
                'featured' => false,

                'cover' => 'https://images.unsplash.com/photo-1497366216548-37526070297c?w=1200',
                'gallery' => [
                    'https://images.unsplash.com/photo-1497366811353-6870744d04b2?w=1200',
                    'https://images.unsplash.com/photo-1497366754035-f200968a6e72?w=1200',
                    'https://images.unsplash.com/photo-1556761175-b413da4baf72?w=1200',
                ],
            ],

            [
                'name' => 'Horizon Beach Club',
                'slug' => 'horizon-beach-club',
                'category' => 'beach',
                'short_description' => 'Private beach access with loungers and a beachfront bar.',
                'description' => 'Exclusive beachfront access with complimentary loungers, umbrellas, and a full-service bar steps from the sand.',
                'building' => 'Beachfront',
                'floor' => 'Ground',
                'wing' => null,
                'pos_x' => 8.2,
                'pos_y' => 88.4,
                'amenities' => [
                    'Private Loungers',
                    'Beach Bar',
                    'Water Sports Rental',
                ],
                'opening_hours' => [
                    'daily' => '08:00-18:00',
                ],
                'phone' => '+1 555 010 2036',
                'email' => 'beach@grandhorizon.example',
                'featured' => true,

                'cover' => 'https://images.unsplash.com/photo-1520250497591-112f2f40a3f4?w=1200',
                'gallery' => [
                    'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?w=1200',
                    'https://images.unsplash.com/photo-1500534623283-312aade485b7?w=1200',
                    'https://images.unsplash.com/photo-1473116763249-2faaef81ccda?w=1200',
                ],
            ],

            [
                'name' => 'Executive Lounge',
                'slug' => 'executive-lounge',
                'category' => 'other',
                'short_description' => 'Complimentary breakfast and evening hors d\'oeuvres for eligible guests.',
                'description' => 'A quiet retreat on the top floor offering complimentary breakfast, all-day refreshments, and evening hors d\'oeuvres for Executive Suite and Honors Diamond guests.',
                'building' => 'Main Building',
                'floor' => '12',
                'wing' => 'Tower',
                'pos_x' => 82.0,
                'pos_y' => 16.5,
                'amenities' => [
                    'Complimentary Breakfast',
                    'Evening Hors d\'oeuvres',
                    'Private Concierge',
                ],
                'opening_hours' => [
                    'daily' => '06:30-22:00',
                ],
                'phone' => '+1 555 010 2037',
                'email' => 'lounge@grandhorizon.example',
                'featured' => true,

                'cover' => 'https://images.unsplash.com/photo-1445019980597-93fa8acb246c?w=1200',
                'gallery' => [
                    'https://images.unsplash.com/photo-1566073771259-6a8506099945?w=1200',
                    'https://images.unsplash.com/photo-1578683010236-d716f9a3f461?w=1200',
                    'https://images.unsplash.com/photo-1590490360182-c33d57733427?w=1200',
                ],
            ],

            // ==========================================================
            // MEETING FACILITIES
            // ==========================================================

            [
                'name' => 'Grand Horizon Ballroom',
                'slug' => 'grand-horizon-ballroom',
                'category' => 'meeting',
                'short_description' => 'Elegant ballroom for weddings, conferences, galas, and large corporate events.',
                'description' => 'The Grand Horizon Ballroom is our largest event venue, featuring flexible seating arrangements, professional AV equipment, dedicated event support, and elegant views of the hotel grounds.',
                'building' => 'Convention Wing',
                'floor' => '1',
                'wing' => 'North Wing',
                'pos_x' => 43.5,
                'pos_y' => 52.8,
                'amenities' => [
                    'Projector',
                    'Large Screen',
                    'Wireless Microphones',
                    'High-Speed WiFi',
                    'Stage',
                    'Dance Floor',
                    'Catering Available',
                    'Private Entrance',
                ],
                'opening_hours' => [
                    'daily' => '06:00-23:00',
                ],
                'phone' => '+1 555 010 2040',
                'email' => 'events@grandhorizon.example',
                'featured' => true,

                'cover' => 'https://images.unsplash.com/photo-1507504031003-b417219a0fde?w=1200',
                'gallery' => [
                    'https://images.unsplash.com/photo-1519167758481-83f550bb49b3?w=1200',
                    'https://images.unsplash.com/photo-1505373877841-8d25f7d46678?w=1200',
                    'https://images.unsplash.com/photo-1492684223066-81342ee5ff30?w=1200',
                    'https://images.unsplash.com/photo-1511578314322-379afb476865?w=1200',
                ],
            ],

            [
                'name' => 'Horizon Boardroom',
                'slug' => 'horizon-boardroom',
                'category' => 'meeting',
                'short_description' => 'Private executive boardroom designed for focused meetings and presentations.',
                'description' => 'A sophisticated private boardroom for executive meetings, presentations, interviews, and confidential discussions. The room includes integrated AV technology and a large conference table.',
                'building' => 'Convention Wing',
                'floor' => '2',
                'wing' => 'East Wing',
                'pos_x' => 65.2,
                'pos_y' => 47.1,
                'amenities' => [
                    'Conference Table',
                    '4K Display',
                    'Video Conferencing',
                    'Wireless Presentation',
                    'High-Speed WiFi',
                    'Whiteboard',
                    'Refreshment Service',
                ],
                'opening_hours' => [
                    'daily' => '07:00-22:00',
                ],
                'phone' => '+1 555 010 2041',
                'email' => 'meetings@grandhorizon.example',
                'featured' => true,

                'cover' => 'https://images.unsplash.com/photo-1497366754035-f200968a6e72?w=1200',
                'gallery' => [
                    'https://images.unsplash.com/photo-1556761175-b413da4baf72?w=1200',
                    'https://images.unsplash.com/photo-1517502884422-41eaead166d4?w=1200',
                    'https://images.unsplash.com/photo-1497366811353-6870744d04b2?w=1200',
                ],
            ],

            [
                'name' => 'Palm Meeting Room',
                'slug' => 'palm-meeting-room',
                'category' => 'meeting',
                'short_description' => 'Flexible meeting room for small teams, workshops, and private events.',
                'description' => 'A bright and flexible meeting space suitable for team meetings, workshops, training sessions, interviews, and small private events. Tables can be arranged to suit different formats.',
                'building' => 'Convention Wing',
                'floor' => '1',
                'wing' => 'South Wing',
                'pos_x' => 37.8,
                'pos_y' => 62.4,
                'amenities' => [
                    'Projector',
                    'Screen',
                    'Whiteboard',
                    'High-Speed WiFi',
                    'Video Conferencing',
                    'Catering Available',
                ],
                'opening_hours' => [
                    'daily' => '07:00-22:00',
                ],
                'phone' => '+1 555 010 2042',
                'email' => 'meetings@grandhorizon.example',
                'featured' => false,

                'cover' => 'https://images.unsplash.com/photo-1497366412874-3415097a27e7?w=1200',
                'gallery' => [
                    'https://images.unsplash.com/photo-1497366216548-37526070297c?w=1200',
                    'https://images.unsplash.com/photo-1497366811353-6870744d04b2?w=1200',
                    'https://images.unsplash.com/photo-1504384308090-c894fdcc538d?w=1200',
                ],
            ],

            [
                'name' => 'Bay Conference Suite',
                'slug' => 'bay-conference-suite',
                'category' => 'meeting',
                'short_description' => 'Premium conference suite with breakout space and panoramic views.',
                'description' => 'The Bay Conference Suite combines a main conference room with an adjoining breakout lounge, making it ideal for executive workshops, seminars, and full-day corporate meetings.',
                'building' => 'Convention Wing',
                'floor' => '3',
                'wing' => 'West Wing',
                'pos_x' => 78.4,
                'pos_y' => 29.6,
                'amenities' => [
                    'Panoramic Windows',
                    '4K Displays',
                    'Video Conferencing',
                    'Breakout Lounge',
                    'High-Speed WiFi',
                    'Wireless Microphones',
                    'Catering Available',
                    'Dedicated Event Host',
                ],
                'opening_hours' => [
                    'daily' => '06:00-23:00',
                ],
                'phone' => '+1 555 010 2043',
                'email' => 'events@grandhorizon.example',
                'featured' => true,

                'cover' => 'https://images.unsplash.com/photo-1497366811353-6870744d04b2?w=1200',
                'gallery' => [
                    'https://images.unsplash.com/photo-1497366216548-37526070297c?w=1200',
                    'https://images.unsplash.com/photo-1556761175-b413da4baf72?w=1200',
                    'https://images.unsplash.com/photo-1504384308090-c894fdcc538d?w=1200',
                    'https://images.unsplash.com/photo-1497366754035-f200968a6e72?w=1200',
                ],
            ],
        ];

        foreach ($facilities as $i => $data) {
            $cover = $data['cover'];
            $gallery = $data['gallery'];

            unset(
                $data['cover'],
                $data['gallery']
            );

            $facility = Facility::create([
                ...$data,
                'hotel_id' => $this->hotel->id,
                'status' => 'published',
                'sort_order' => $i,
            ]);

            // Cover
            $this->attachCover(
                $facility,
                $cover,
                $facility->name
            );

            // Gallery
            foreach ($gallery as $index => $image) {
                $this->attachGalleryImage(
                    $facility,
                    $image,
                    $facility->name.' Gallery '.($index + 1),
                    $index
                );
            }
        }
    }

    protected function seedRestaurants(): void
    {
        $azure = Restaurant::create([
            'hotel_id' => $this->hotel->id,
            'name' => 'Azure Restaurant',
            'slug' => 'azure-restaurant',
            'description' => 'Fine dining with panoramic sea views, blending Mediterranean and contemporary cuisine.',
            'cuisine' => 'Mediterranean',
            'location' => 'Ground Floor, Sea View Wing',
            'floor' => '1',
            'opening_hours' => ['breakfast' => '06:30-10:30', 'lunch' => '12:00-14:30', 'dinner' => '18:30-22:30'],
            'dress_code' => 'Smart Casual',
            'phone' => '+1 555 010 2040',
            'status' => 'published',
            'featured' => true,
            'sort_order' => 0,
        ]);
        $this->attachCover($azure, 'https://images.unsplash.com/photo-1552566626-52f8b828add9?w=800', 'Azure Restaurant');

        $skyLounge = Restaurant::create([
            'hotel_id' => $this->hotel->id,
            'name' => 'Sky Lounge',
            'slug' => 'sky-lounge',
            'description' => 'Rooftop cocktail bar and lounge with live DJ sets on weekends.',
            'cuisine' => 'Small Plates & Cocktails',
            'location' => 'Rooftop',
            'floor' => 'Roof',
            'opening_hours' => ['daily' => '17:00-01:00'],
            'dress_code' => 'Smart Casual',
            'phone' => '+1 555 010 2041',
            'status' => 'published',
            'featured' => false,
            'sort_order' => 1,
        ]);
        $this->attachCover($skyLounge, 'https://images.unsplash.com/photo-1541532713592-79a0317b6b77?w=800', 'Sky Lounge');

        $herbNKitchen = Restaurant::create([
            'hotel_id' => $this->hotel->id,
            'name' => 'Herb N\' Kitchen',
            'slug' => 'herb-n-kitchen',
            'description' => 'Hilton\'s all-day dining concept, serving globally inspired dishes made with fresh, local ingredients.',
            'cuisine' => 'International',
            'location' => 'Lobby Level',
            'floor' => 'G',
            'opening_hours' => ['daily' => '06:00-23:00'],
            'dress_code' => 'Casual',
            'phone' => '+1 555 010 2042',
            'status' => 'published',
            'featured' => true,
            'sort_order' => 2,
        ]);
        $this->attachCover($herbNKitchen, 'https://images.unsplash.com/photo-1414235077428-338989a2e8c0?w=800', 'Herb N\' Kitchen');

        $this->seedMenu($azure, [
            'Breakfast' => [
                ['Hilton Grand Horizon Breakfast', 'Eggs any style, pastries, seasonal fruit, fresh juice.', 22.00, ['vegetarian']],
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

        $this->seedMenu($herbNKitchen, [
            'All Day' => [
                ['Herb N\' Burger', 'Angus beef, aged cheddar, house sauce, fries.', 19.00, []],
                ['Garden Bowl', 'Quinoa, roasted vegetables, tahini dressing.', 17.00, ['vegan', 'gluten-free']],
                ['Margherita Flatbread', 'San Marzano tomato, buffalo mozzarella, basil.', 16.00, ['vegetarian']],
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
                    'name' => $name,
                    'description' => $description,
                    'price' => $price,
                    'dietary_info' => $dietary,
                    'is_available' => true,
                    'sort_order' => $i,
                ]);
            }
        }
    }

    protected function seedServices(): void
    {
        // Each service now carries real timing info (`availability`, the
        // hours/days it can be reached or fulfilled) and a `contact` value
        // — the short in-house extension a guest dials from the room phone,
        // distinct from the hotel's full outside line.
        $services = [
            [
                'name' => 'Room Service',
                'slug' => 'room-service',
                'desc' => '24-hour in-room dining from the Azure Restaurant and Herb N\' Kitchen menus.',
                'icon' => 'utensils',
                'requestEnabled' => true,
                'price' => null,
                'availability' => ['daily' => '00:00-23:59'],
                'contact' => 'Ext. 700',
            ],
            [
                'name' => 'Laundry & Dry Cleaning',
                'slug' => 'laundry',
                'desc' => 'Same-day laundry and dry cleaning when requested before 9:00 AM.',
                'icon' => 'shirt',
                'requestEnabled' => true,
                'price' => 12.00,
                'availability' => ['daily' => '07:00-19:00'],
                'contact' => 'Ext. 610',
            ],
            [
                'name' => 'Housekeeping',
                'slug' => 'housekeeping',
                'desc' => 'Daily housekeeping, available on request for evening turndown.',
                'icon' => 'sparkles',
                'requestEnabled' => true,
                'price' => null,
                'availability' => ['daily' => '08:00-20:00'],
                'contact' => 'Ext. 620',
            ],
            [
                'name' => 'Concierge',
                'slug' => 'concierge',
                'desc' => 'Reservations, recommendations, and local experiences arranged for you.',
                'icon' => 'bell',
                'requestEnabled' => false,
                'price' => null,
                'availability' => ['daily' => '06:00-23:00'],
                'contact' => 'Ext. 0',
            ],
            [
                'name' => 'Airport Transfer',
                'slug' => 'airport-transfer',
                'desc' => 'Private car service to and from the airport, booked at least 2 hours ahead.',
                'icon' => 'car',
                'requestEnabled' => true,
                'price' => 45.00,
                'availability' => ['daily' => '00:00-23:59'],
                'contact' => 'Ext. 730',
            ],
            [
                'name' => 'Valet Parking',
                'slug' => 'valet-parking',
                'desc' => 'Valet parking with unlimited in-and-out privileges for the length of stay.',
                'icon' => 'car-front',
                'requestEnabled' => false,
                'price' => 35.00,
                'availability' => ['daily' => '00:00-23:59'],
                'contact' => 'Ext. 100',
            ],
            [
                'name' => 'IT / Tech Support',
                'slug' => 'tech-support',
                'desc' => 'In-room WiFi troubleshooting, device connection, and AV setup assistance.',
                'icon' => 'wifi',
                'requestEnabled' => true,
                'price' => null,
                'availability' => ['daily' => '07:00-23:00'],
                'contact' => 'Ext. 640',
            ],
            [
                'name' => 'Wake-up Call',
                'slug' => 'wake-up-call',
                'desc' => 'Schedule a courtesy wake-up call, by phone or through the app.',
                'icon' => 'alarm-clock',
                'requestEnabled' => true,
                'price' => null,
                'availability' => ['daily' => '00:00-23:59'],
                'contact' => 'Ext. 0',
            ],
        ];

        foreach ($services as $i => $data) {
            Service::create([
                'hotel_id' => $this->hotel->id,
                'name' => $data['name'],
                'slug' => $data['slug'],
                'description' => $data['desc'],
                'icon' => $data['icon'],
                'availability' => $data['availability'],
                'contact' => $data['contact'],
                'request_enabled' => $data['requestEnabled'],
                'price' => $data['price'],
                'status' => 'published',
                'sort_order' => $i,
            ]);
        }
    }

    protected function seedEvents(): void
    {
        $events = [
            ['Live Music Night', 'live-music-night', 'Acoustic sets on the Sky Lounge terrace.', 'Sky Lounge', now()->addDays(2), 'https://images.unsplash.com/photo-1493225457124-a3eb161ffa5f?w=800'],
            ['Mediterranean Night', 'mediterranean-night', 'A themed buffet dinner celebrating coastal flavors.', 'Azure Restaurant', now()->addDays(5), 'https://images.unsplash.com/photo-1414235077428-338989a2e8c0?w=800'],
            ['Rooftop Pool Party', 'rooftop-pool-party', 'DJ sets and cocktails at the Horizon Rooftop Pool.', 'Horizon Rooftop Pool', now()->addDays(9), 'https://images.unsplash.com/photo-1571896349842-33c89424de2d?w=800'],
            ['Sunset Yoga', 'sunset-yoga', 'Guided yoga session as the sun sets over the bay.', 'Horizon Beach Club', now()->addDays(1), 'https://images.unsplash.com/photo-1544367567-0f2fcb009e0b?w=800'],
            ['Hilton Honors Member Mixer', 'hilton-honors-member-mixer', 'Cocktails and canapés to thank our Honors members.', 'Executive Lounge', now()->addDays(7), 'https://images.unsplash.com/photo-1445019980597-93fa8acb246c?w=800'],
        ];

        foreach ($events as [$title, $slug, $desc, $location, $date, $image]) {
            $event = Event::create([
                'hotel_id' => $this->hotel->id,
                'title' => $title,
                'slug' => $slug,
                'description' => $desc,
                'location' => $location,
                'start_date' => $date,
                'start_time' => '19:00:00',
                'booking_required' => false,
                'status' => 'published',
            ]);
            $this->attachCover($event, $image, $title);
        }
    }

    protected function seedOffers(): void
    {
        $offers = [
            ['Spa Package', 'spa-package', '90-minute massage plus full Eforea Spa thermal suite access.', 149.00, 15, true, 'https://images.unsplash.com/photo-1540555700478-4be289fbecef?w=800'],
            ['Dinner for Two', 'dinner-for-two', 'Three-course dinner at Azure Restaurant with a bottle of wine.', 120.00, 10, false, 'https://images.unsplash.com/photo-1414235077428-338989a2e8c0?w=800'],
            ['Weekend Escape', 'weekend-escape', 'Two-night stay in a King Guest Room with breakfast and late checkout.', 480.00, 20, true, 'https://images.unsplash.com/photo-1566073771259-6a8506099945?w=800'],
            ['Family Package', 'family-package', 'Double Queen Room, Kids Club access, and daily breakfast for four.', 620.00, 12, false, 'https://images.unsplash.com/photo-1602002418082-a4443e081dd1?w=800'],
            ['Honors Member Rate', 'honors-member-rate', 'Exclusive discounted rate for Hilton Honors members, booked direct.', 210.00, 18, true, 'https://images.unsplash.com/photo-1590490360182-c33d57733427?w=800'],
        ];

        foreach ($offers as [$title, $slug, $desc, $price, $discount, $featured, $image]) {
            $offer = Offer::create([
                'hotel_id' => $this->hotel->id,
                'title' => $title,
                'slug' => $slug,
                'description' => $desc,
                'price' => $price,
                'discount' => $discount,
                'featured' => $featured,
                'valid_from' => now(),
                'valid_until' => now()->addMonths(2),
                'status' => 'published',
            ]);
            $this->attachCover($offer, $image, $title);
        }
    }

    protected function seedExperiences(): void
    {
        $experiences = [
            ['Sunrise Yoga', 'sunrise-yoga', 'wellness', 'Morning yoga overlooking the sea.', '60 minutes', 25.00, true, 'https://images.unsplash.com/photo-1544367567-0f2fcb009e0b?w=800'],
            ['Chef\'s Table Cooking Class', 'cooking-class', 'culinary', 'Learn Mediterranean recipes with our executive chef.', '2 hours', 65.00, true, 'https://images.unsplash.com/photo-1556910103-1c02745aae4d?w=800'],
            ['Private Sunset Dinner', 'sunset-dinner', 'culinary', 'A private beachfront dinner as the sun sets.', '2 hours', 180.00, true, 'https://images.unsplash.com/photo-1414235077428-338989a2e8c0?w=800'],
            ['Kids Adventure Day', 'kids-adventure-day', 'family', 'A full day of themed games and crafts for children.', 'Full day', 0.00, false, 'https://images.unsplash.com/photo-1560184897-ae75f418493e?w=800'],
            ['Bay Sunset Cruise', 'bay-sunset-cruise', 'adventure', 'A two-hour catamaran cruise along the bay at golden hour.', '2 hours', 95.00, true, 'https://images.unsplash.com/photo-1500375592092-40eb2168fd21?w=800'],
        ];

        foreach ($experiences as $i => [$title, $slug, $category, $desc, $duration, $price, $featured, $image]) {
            $experience = Experience::create([
                'hotel_id' => $this->hotel->id,
                'title' => $title,
                'slug' => $slug,
                'category' => $category,
                'description' => $desc,
                'duration' => $duration,
                'price' => $price,
                'featured' => $featured,
                'status' => 'published',
                'sort_order' => $i,
            ]);
            $this->attachCover($experience, $image, $title);
        }
    }

    protected function seedInfoEntries(): void
    {
        $entries = [
            // =====================================================
            // HOTEL TIMINGS
            // =====================================================
            [
                'kind' => InfoEntry::KIND_TIMING,
                'group' => 'hotel',
                'label' => 'Check-in',
                'value' => '15:00',
                'sort_order' => 1,
            ],
            [
                'kind' => InfoEntry::KIND_TIMING,
                'group' => 'hotel',
                'label' => 'Check-out',
                'value' => '12:00',
                'sort_order' => 2,
            ],
            [
                'kind' => InfoEntry::KIND_TIMING,
                'group' => 'front_desk',
                'label' => 'Front Desk',
                'value' => '24 hours',
                'sort_order' => 3,
            ],

            // =====================================================
            // DINING
            // =====================================================
            [
                'kind' => InfoEntry::KIND_TIMING,
                'group' => 'dining',
                'label' => 'Breakfast',
                'value' => '06:30-10:30',
                'sort_order' => 10,
            ],
            [
                'kind' => InfoEntry::KIND_TIMING,
                'group' => 'dining',
                'label' => 'Lunch',
                'value' => '12:00-15:00',
                'sort_order' => 11,
            ],
            [
                'kind' => InfoEntry::KIND_TIMING,
                'group' => 'dining',
                'label' => 'Dinner',
                'value' => '18:00-23:00',
                'sort_order' => 12,
            ],
            [
                'kind' => InfoEntry::KIND_TIMING,
                'group' => 'dining',
                'label' => 'Room Service',
                'value' => '24 hours',
                'sort_order' => 13,
            ],

            // =====================================================
            // FACILITIES
            // =====================================================
            [
                'kind' => InfoEntry::KIND_TIMING,
                'group' => 'pool',
                'label' => 'Pool',
                'value' => '07:00-19:00',
                'sort_order' => 20,
            ],
            [
                'kind' => InfoEntry::KIND_TIMING,
                'group' => 'fitness',
                'label' => 'Fitness Center',
                'value' => '24 hours',
                'sort_order' => 21,
            ],
            [
                'kind' => InfoEntry::KIND_TIMING,
                'group' => 'spa',
                'label' => 'Spa',
                'value' => '09:00-20:00',
                'sort_order' => 22,
            ],
            [
                'kind' => InfoEntry::KIND_TIMING,
                'group' => 'kids',
                'label' => 'Kids Club',
                'value' => '09:00-18:00',
                'sort_order' => 23,
            ],
            [
                'kind' => InfoEntry::KIND_TIMING,
                'group' => 'executive_lounge',
                'label' => 'Executive Lounge',
                'value' => '06:30-22:00',
                'sort_order' => 24,
            ],

            // =====================================================
            // SHORT CALL NUMBERS
            // =====================================================
            [
                'kind' => InfoEntry::KIND_SHORT_CALL,
                'group' => 'guest_services',
                'label' => 'Guest Services',
                'value' => '0',
                'sort_order' => 30,
            ],
            [
                'kind' => InfoEntry::KIND_SHORT_CALL,
                'group' => 'front_desk',
                'label' => 'Front Desk',
                'value' => '0',
                'sort_order' => 31,
            ],
            [
                'kind' => InfoEntry::KIND_SHORT_CALL,
                'group' => 'concierge',
                'label' => 'Concierge',
                'value' => '0',
                'sort_order' => 32,
            ],
            [
                'kind' => InfoEntry::KIND_SHORT_CALL,
                'group' => 'housekeeping',
                'label' => 'Housekeeping',
                'value' => '0',
                'sort_order' => 33,
            ],
            [
                'kind' => InfoEntry::KIND_SHORT_CALL,
                'group' => 'room_service',
                'label' => 'Room Service',
                'value' => '0',
                'sort_order' => 34,
            ],
            [
                'kind' => InfoEntry::KIND_SHORT_CALL,
                'group' => 'emergency',
                'label' => 'Emergency',
                'value' => '911',
                'sort_order' => 35,
            ],
        ];

        foreach ($entries as $entry) {
            InfoEntry::create([
                'hotel_id' => $this->hotel->id,
                ...$entry,
            ]);
        }
    }

    protected function seedMedia(
        Facility $facility,
        string $cover,
        array $gallery
    ): void {
        $this->createMedia(
            $facility,
            'cover',
            $cover,
            0
        );

        foreach ($gallery as $index => $image) {
            $this->createMedia(
                $facility,
                'gallery',
                $image,
                $index
            );
        }
    }

    protected function createMedia(
        Facility $facility,
        string $collection,
        string $url,
        int $sortOrder = 0
    ): void {
        Media::create([
            'mediable_type' => Facility::class,
            'mediable_id' => $facility->id,
            'collection' => $collection,
            'url' => $url,
            'sort_order' => $sortOrder,
        ]);
    }
}
