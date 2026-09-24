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
        Media::updateOrCreate(
            [
                'hotel_id' => $this->hotel->id,
                'mediable_type' => get_class($model),
                'mediable_id' => $model->id,
                'collection' => 'cover',
            ],
            [
                'disk' => 'external',
                'path' => $url,
                'alt_text' => $alt,
                'sort_order' => 0,
            ]
        );
    }

    protected function attachGalleryImage(
        Model $model,
        string $url,
        ?string $alt = null,
        int $sortOrder = 0
    ): void {
        Media::updateOrCreate(
            [
                'hotel_id' => $this->hotel->id,
                'mediable_type' => get_class($model),
                'mediable_id' => $model->id,
                'collection' => 'gallery',
                'sort_order' => $sortOrder,
            ],
            [
                'disk' => 'external',
                'path' => $url,
                'alt_text' => $alt,
            ]
        );
    }

    protected function seedRooms(): void
    {
        $rooms = [
            [
                'name' => 'Deluxe Nile View Room',
                'slug' => 'deluxe-nile-view-room',
                'short_description' => 'Elegant room with sweeping Nile River panoramas and modern comforts.',
                'description' => 'Wake up to breathtaking views of the River Nile from your Deluxe Nile View Room, featuring a Serta pillow-top king bed, floor-to-ceiling windows, marble bathroom with rain shower, and a dedicated work desk.',
                'size_sqm' => 38,
                'max_guests' => 2,
                'bed_type' => '1 King Bed',
                'view' => 'Nile River View',
                'features' => ['Free WiFi', '55" Smart TV', 'Rainfall Shower', 'Nespresso Machine', 'In-room Safe'],
                'featured' => false,
                'image' => 'https://images.unsplash.com/photo-1611892440504-42a792e24d32?w=1000',
            ],
            [
                'name' => 'Superior City View Room',
                'slug' => 'superior-city-view-room',
                'short_description' => 'Spacious room overlooking the vibrant skyline of Cairo.',
                'description' => 'Our Superior City View Room offers modern Egyptian-inspired décor, a plush king bed, and panoramic city-skyline views. The spacious marble bathroom features a deep soaking tub and separate rainfall shower.',
                'size_sqm' => 35,
                'max_guests' => 2,
                'bed_type' => '1 King Bed',
                'view' => 'Cairo City View',
                'features' => ['Free WiFi', '55" Smart TV', 'Soaking Tub', 'Bathrobe & Slippers', 'Mini Fridge'],
                'featured' => false,
                'image' => 'https://images.unsplash.com/photo-1590490360182-c33d57733427?w=1000',
            ],
            [
                'name' => 'Executive Nile Suite',
                'slug' => 'executive-nile-suite',
                'short_description' => 'A separate living area with uninterrupted Nile views and Executive Lounge access.',
                'description' => 'The Executive Nile Suite offers a grand separate living room and master bedroom, both facing the iconic Nile River. Complimentary access to the Executive Lounge, turndown service, and personalized butler assistance complete this exceptional suite experience.',
                'size_sqm' => 65,
                'max_guests' => 3,
                'bed_type' => '1 King Bed',
                'view' => 'Nile River View',
                'features' => ['Executive Lounge Access', 'Separate Living Room', 'Soaking Tub', 'Bathrobe & Slippers', 'Premium Minibar'],
                'featured' => true,
                'image' => 'https://images.unsplash.com/photo-1590073242678-70ee3fc28f8e?w=1000',
            ],
            [
                'name' => 'Twin Nile View Room',
                'slug' => 'twin-nile-view-room',
                'short_description' => 'Two twin beds with direct Nile River views, ideal for colleagues or friends.',
                'description' => 'Perfect for two guests traveling together, this elegantly appointed room offers two plush twin beds, floor-to-ceiling Nile River panoramas, and all the modern amenities expected from Smarttel Hotel.',
                'size_sqm' => 36,
                'max_guests' => 2,
                'bed_type' => '2 Twin Beds',
                'view' => 'Nile River View',
                'features' => ['Free WiFi', '55" Smart TV', 'Rainfall Shower', 'Work Desk', 'Blackout Curtains'],
                'featured' => false,
                'image' => 'https://images.unsplash.com/photo-1560185893-a55cbc8c57e8?w=1000',
            ],
            [
                'name' => 'Presidential Suite',
                'slug' => 'presidential-suite',
                'short_description' => 'The pinnacle of luxury — a top-floor suite with private terrace and Nile vistas.',
                'description' => 'The crown of Smarttel Hotel Cairo: a top-floor Presidential Suite featuring a private terrace, a formal dining area for eight, a master bedroom with a king bed, a separate study, and breathtaking 270-degree Nile and city views. Butler service and private limousine included.',
                'size_sqm' => 120,
                'max_guests' => 4,
                'bed_type' => '1 King Bed + Sofa Bed',
                'view' => 'Panoramic Nile & City View',
                'features' => ['Private Terrace', 'Dining Area for 8', 'Butler Service', 'Jacuzzi Tub', 'Complimentary Airport Transfer'],
                'featured' => true,
                'image' => 'https://images.unsplash.com/photo-1582719508461-905c673771fd?w=1000',
            ],
            [
                'name' => 'Accessible Deluxe Room',
                'slug' => 'accessible-deluxe-room',
                'short_description' => 'Fully ADA-compliant room with roll-in shower and accessible amenities.',
                'description' => 'Thoughtfully designed for guests with mobility needs, this accessible room features a roll-in shower, grab bars, wider doorways, and lower furniture rails, delivering full Smarttel comfort without compromise.',
                'size_sqm' => 36,
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

            $room = Room::updateOrCreate(
                [
                    'hotel_id' => $this->hotel->id,
                    'slug' => $data['slug'],
                ],
                array_merge($data, [
                    'hotel_id' => $this->hotel->id,
                    'status' => 'published',
                    'sort_order' => $i,
                ])
            );

            $this->attachCover($room, $image, $room->name);
        }
    }

    protected function seedFacilities(): void
    {
        $facilities = [
            [
                'name' => 'Nile Spa & Wellness',
                'slug' => 'nile-spa-wellness',
                'category' => 'wellness',
                'short_description' => 'Immersive spa retreat inspired by ancient Egyptian beauty rituals.',
                'description' => 'The Nile Spa & Wellness at Smarttel Hotel Cairo draws on ancient Egyptian beauty secrets — cleopatra milk baths, papyrus-extract facials, and hot-stone massage treatments — blended with modern therapeutic techniques in a serene Nile-facing sanctuary.',
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
                    'Couples Treatment Room',
                ],
                'opening_hours' => [
                    'mon_fri' => '09:00-21:00',
                    'sat_sun' => '09:00-22:00',
                ],
                'phone' => '+20 2 2578 0444',
                'email' => 'spa@smarttelhotel.com',
                'featured' => true,

                'cover' => 'https://images.unsplash.com/photo-1540555700478-4be289fbecef?w=1200',
                'gallery' => [
                    'https://images.unsplash.com/photo-1544161515-4ab6ce6db874?w=1200',
                    'https://images.unsplash.com/photo-1600334089648-b0d9d3028eb2?w=1200',
                    'https://images.unsplash.com/photo-1540555700478-4be289fbecef?w=1200',
                ],
            ],

            [
                'name' => 'Pharaoh Fitness Club',
                'slug' => 'pharaoh-fitness-club',
                'category' => 'fitness',
                'short_description' => '24-hour fully equipped gym with Nile panoramic views.',
                'description' => 'Pharaoh Fitness Club spans two floors of state-of-the-art equipment including Technogym machines, Peloton bikes, free weights, and a dedicated yoga & pilates studio with panoramic Nile River views. Personal trainers available on request.',
                'building' => 'Main Building',
                'floor' => '1',
                'wing' => 'East Wing',
                'pos_x' => 61.4,
                'pos_y' => 42.8,
                'amenities' => [
                    'Peloton Bikes',
                    'Free Weights',
                    'Cardio Machines',
                    'Yoga Studio',
                    'Personal Training',
                ],
                'opening_hours' => [
                    'daily' => '00:00-23:59',
                ],
                'phone' => '+20 2 2578 0444',
                'email' => 'fitness@smarttelhotel.com',
                'featured' => false,

                'cover' => 'https://images.unsplash.com/photo-1534438327276-14e5300c3a48?w=1200',
                'gallery' => [
                    'https://images.unsplash.com/photo-1571902943202-507ec2618e8f?w=1200',
                    'https://images.unsplash.com/photo-1581009146145-b5ef050c2e1e?w=1200',
                    'https://images.unsplash.com/photo-1593079831268-3381b0db4a77?w=1200',
                ],
            ],

            [
                'name' => 'Nile Rooftop Pool',
                'slug' => 'nile-rooftop-pool',
                'category' => 'pool',
                'short_description' => 'Rooftop infinity pool with breathtaking Nile River panoramas.',
                'description' => 'Our rooftop infinity pool seamlessly merges with the Nile horizon. Enjoy a swim with 360° Cairo skyline and river views, with a swim-up bar, private cabanas, and full towel service.',
                'building' => 'Rooftop',
                'floor' => 'Roof',
                'wing' => null,
                'pos_x' => 50.0,
                'pos_y' => 8.5,
                'amenities' => [
                    'Swim-up Bar',
                    'Private Cabanas',
                    'Towel Service',
                    'Nile View Sunbeds',
                ],
                'opening_hours' => [
                    'daily' => '07:00-20:00',
                ],
                'phone' => '+20 2 2578 0444',
                'email' => 'pool@smarttelhotel.com',
                'featured' => true,

                'cover' => 'https://images.unsplash.com/photo-1571896349842-33c89424de2d?w=1200',
                'gallery' => [
                    'https://images.unsplash.com/photo-1602002418082-a4443e081dd1?w=1200',
                    'https://images.unsplash.com/photo-1572331165267-854da2b10ccc?w=1200',
                    'https://images.unsplash.com/photo-1564501049412-61c2a3083791?w=1200',
                ],
            ],

            [
                'name' => 'Smarttel Kids Club',
                'slug' => 'smarttel-kids-club',
                'category' => 'kids',
                'short_description' => 'Supervised activities and play areas for ages 4–12.',
                'description' => 'A vibrant, safe play world where children aged 4–12 enjoy supervised games, Egyptian heritage crafts, storytelling, and daily themed activities — while parents enjoy the hotel.',
                'building' => 'Garden Pavilion',
                'floor' => '1',
                'wing' => null,
                'pos_x' => 18.7,
                'pos_y' => 67.2,
                'amenities' => [
                    'Supervised Play',
                    'Egyptian Crafts',
                    'Outdoor Play Area',
                    'Movie Corner',
                ],
                'opening_hours' => [
                    'daily' => '09:00-18:00',
                ],
                'phone' => '+20 2 2578 0444',
                'email' => 'kids@smarttelhotel.com',
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
                'description' => 'Our Executive Business Center offers private workstations, high-speed fiber internet, printing services, and bookable meeting rooms for up to 12 people — serving both leisure and corporate Cairo visitors.',
                'building' => 'Main Building',
                'floor' => '3',
                'wing' => 'East Wing',
                'pos_x' => 72.1,
                'pos_y' => 34.6,
                'amenities' => [
                    'High-Speed WiFi',
                    'Printing',
                    'Video Conferencing',
                    'Private Workstations',
                ],
                'opening_hours' => [
                    'daily' => '07:00-22:00',
                ],
                'phone' => '+20 2 2578 0444',
                'email' => 'business@smarttelhotel.com',
                'featured' => false,

                'cover' => 'https://images.unsplash.com/photo-1497366216548-37526070297c?w=1200',
                'gallery' => [
                    'https://images.unsplash.com/photo-1497366811353-6870744d04b2?w=1200',
                    'https://images.unsplash.com/photo-1497366754035-f200968a6e72?w=1200',
                    'https://images.unsplash.com/photo-1556761175-b413da4baf72?w=1200',
                ],
            ],

            [
                'name' => 'Executive Lounge',
                'slug' => 'executive-lounge',
                'category' => 'other',
                'short_description' => 'Exclusive top-floor retreat with complimentary breakfast and evening snacks.',
                'description' => 'A refined sanctuary on the top floor of Smarttel Hotel Cairo, offering complimentary breakfast, all-day refreshments, and evening mezze & canapés. Exclusively for Executive Suite guests and Hilton Honors Diamond members.',
                'building' => 'Main Building',
                'floor' => '14',
                'wing' => 'Tower',
                'pos_x' => 82.0,
                'pos_y' => 16.5,
                'amenities' => [
                    'Complimentary Breakfast',
                    'Evening Mezze & Canapés',
                    'Private Concierge',
                    'Nile View Terrace',
                ],
                'opening_hours' => [
                    'daily' => '06:30-22:00',
                ],
                'phone' => '+20 2 2578 0444',
                'email' => 'lounge@smarttelhotel.com',
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
                'name' => 'Grand Nile Ballroom',
                'slug' => 'grand-nile-ballroom',
                'category' => 'meeting',
                'short_description' => 'Elegant ballroom for weddings, galas, and large corporate events.',
                'description' => 'The Grand Nile Ballroom at Smarttel Hotel Cairo seats up to 600 guests and features flexible seating configurations, state-of-the-art AV, a dedicated events team, and sweeping Nile River views — making it Cairo\'s premier event venue.',
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
                'phone' => '+20 2 2578 0444',
                'email' => 'events@smarttelhotel.com',
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
                'name' => 'Nile Boardroom',
                'slug' => 'nile-boardroom',
                'category' => 'meeting',
                'short_description' => 'Private executive boardroom with Nile River views and full AV.',
                'description' => 'A sophisticated private boardroom for executive meetings, board presentations, and confidential sessions. Features an integrated 4K display, high-speed fibre, video conferencing, and floor-to-ceiling Nile River panoramas.',
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
                'phone' => '+20 2 2578 0444',
                'email' => 'meetings@smarttelhotel.com',
                'featured' => true,

                'cover' => 'https://images.unsplash.com/photo-1497366754035-f200968a6e72?w=1200',
                'gallery' => [
                    'https://images.unsplash.com/photo-1556761175-b413da4baf72?w=1200',
                    'https://images.unsplash.com/photo-1517502884422-41eaead166d4?w=1200',
                    'https://images.unsplash.com/photo-1497366811353-6870744d04b2?w=1200',
                ],
            ],

            [
                'name' => 'Pharaoh Meeting Room',
                'slug' => 'pharaoh-meeting-room',
                'category' => 'meeting',
                'short_description' => 'Flexible meeting room for small teams, workshops, and private events.',
                'description' => 'A bright and flexible meeting space suitable for team meetings, workshops, training sessions, and small private events. Tables can be arranged in theatre, classroom, or boardroom format.',
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
                'phone' => '+20 2 2578 0444',
                'email' => 'meetings@smarttelhotel.com',
                'featured' => false,

                'cover' => 'https://images.unsplash.com/photo-1497366412874-3415097a27e7?w=1200',
                'gallery' => [
                    'https://images.unsplash.com/photo-1497366216548-37526070297c?w=1200',
                    'https://images.unsplash.com/photo-1497366811353-6870744d04b2?w=1200',
                    'https://images.unsplash.com/photo-1504384308090-c894fdcc538d?w=1200',
                ],
            ],

            [
                'name' => 'Nile Conference Suite',
                'slug' => 'nile-conference-suite',
                'category' => 'meeting',
                'short_description' => 'Premium conference suite with breakout area and panoramic Nile views.',
                'description' => 'The Nile Conference Suite at Smarttel Hotel Cairo combines a main conference hall for 120 delegates with an adjoining breakout lounge, making it ideal for executive workshops, seminars, and full-day corporate meetings with Nile River views.',
                'building' => 'Convention Wing',
                'floor' => '3',
                'wing' => 'West Wing',
                'pos_x' => 78.4,
                'pos_y' => 29.6,
                'amenities' => [
                    'Panoramic Nile Windows',
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
                'phone' => '+20 2 2578 0444',
                'email' => 'events@smarttelhotel.com',
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

            $facility = Facility::updateOrCreate(
                [
                    'hotel_id' => $this->hotel->id,
                    'slug' => $data['slug'],
                ],
                [
                    ...$data,
                    'hotel_id' => $this->hotel->id,
                    'status' => 'published',
                    'sort_order' => $i,
                ]
            );

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
        $nileGrill = Restaurant::updateOrCreate(
            ['hotel_id' => $this->hotel->id, 'slug' => 'nile-grill'],
            [
                'hotel_id' => $this->hotel->id,
                'name' => 'Nile Grill',
                'slug' => 'nile-grill',
                'description' => 'Smarttel Hotel Cairo\'s legendary Nile-view steakhouse, famed for prime cuts, fresh seafood, and panoramic River Nile vistas.',
                'cuisine' => 'Grills & Seafood',
                'location' => 'Ground Floor, Nile Wing',
                'floor' => '1',
                'opening_hours' => ['breakfast' => '07:00-10:30', 'lunch' => '12:30-15:00', 'dinner' => '19:00-23:00'],
                'dress_code' => 'Smart Casual',
                'phone' => '+20 2 2578 0444',
                'status' => 'published',
                'featured' => true,
                'sort_order' => 0,
            ]
        );
        $this->attachCover($nileGrill, 'https://images.unsplash.com/photo-1552566626-52f8b828add9?w=800', 'Nile Grill');

        $cairoCafe = Restaurant::updateOrCreate(
            ['hotel_id' => $this->hotel->id, 'slug' => 'cairo-cafe-terrace'],
            [
                'hotel_id' => $this->hotel->id,
                'name' => 'Cairo Café & Terrace',
                'slug' => 'cairo-cafe-terrace',
                'description' => 'A rooftop café and lounge offering panoramic Nile and Cairo skyline views, signature Egyptian shisha, specialty coffees, and light Mediterranean mezze.',
                'cuisine' => 'Café & Mezze',
                'location' => 'Rooftop Terrace',
                'floor' => 'Roof',
                'opening_hours' => ['daily' => '10:00-01:00'],
                'dress_code' => 'Casual',
                'phone' => '+20 2 2578 0444',
                'status' => 'published',
                'featured' => false,
                'sort_order' => 1,
            ]
        );
        $this->attachCover($cairoCafe, 'https://images.unsplash.com/photo-1541532713592-79a0317b6b77?w=800', 'Cairo Café & Terrace');

        $sarayaEgyptian = Restaurant::updateOrCreate(
            ['hotel_id' => $this->hotel->id, 'slug' => 'saraya-egyptian-kitchen'],
            [
                'hotel_id' => $this->hotel->id,
                'name' => 'Saraya Egyptian Kitchen',
                'slug' => 'saraya-egyptian-kitchen',
                'description' => 'An all-day dining showcase of authentic Egyptian cuisine, from Ful Medames and Koshari to slow-roasted lamb and freshly baked aish baladi, served in a richly decorated dining hall.',
                'cuisine' => 'Egyptian & International',
                'location' => 'Lobby Level',
                'floor' => 'G',
                'opening_hours' => ['daily' => '06:00-23:30'],
                'dress_code' => 'Casual',
                'phone' => '+20 2 2578 0444',
                'status' => 'published',
                'featured' => true,
                'sort_order' => 2,
            ]
        );
        $this->attachCover($sarayaEgyptian, 'https://images.unsplash.com/photo-1414235077428-338989a2e8c0?w=800', 'Saraya Egyptian Kitchen');

        $this->seedMenu($nileGrill, [
            'Breakfast' => [
                ['Smarttel Hotel Full Breakfast', 'Eggs any style, ful medames, white cheese, seasonal fruit, fresh juice.', 250.00, ['vegetarian']],
                ['Egyptian Cheese & Tomato Toast', 'Freshly baked aish, white cheese, tomato, olive oil.', 120.00, ['vegetarian']],
            ],
            'Main Courses' => [
                ['Nile Grilled Sea Bass', 'Lemon herb butter, roasted vegetables, saffron rice.', 480.00, ['gluten-free']],
                ['Slow-Roasted Egyptian Lamb', 'Slow-cooked lamb shoulder, apricots, couscous, za\'atar jus.', 550.00, []],
                ['Prime Ribeye Steak', 'Australian 300g ribeye, truffle fries, garlic butter.', 750.00, []],
            ],
            'Desserts' => [
                ['Om Ali', 'Egypt\'s beloved bread pudding with cream, nuts, and raisins.', 95.00, ['vegetarian']],
                ['Konafa & Cream', 'Crispy shredded pastry, kashta cream, rose water syrup.', 80.00, ['vegetarian']],
            ],
        ]);

        $this->seedMenu($cairoCafe, [
            'Drinks' => [
                ['Egyptian Herbal Tea', 'Premium karkade, mint, or chamomile — served hot or iced.', 55.00, ['vegan']],
                ['Specialty Arabic Coffee', 'Cardamom-spiced Arabica, served with dates.', 65.00, ['vegan']],
                ['Nile Sunset Cocktail', 'Signature non-alcoholic hibiscus citrus with mint.', 85.00, []],
            ],
            'Starters' => [
                ['Egyptian Mezze Board', 'Hummus, baba ganoush, ful, white cheese, olives, aish baladi.', 180.00, ['vegetarian']],
                ['Hawawshi Roll', 'Spiced minced meat in crispy bread with tomato salsa.', 120.00, []],
            ],
        ]);

        $this->seedMenu($sarayaEgyptian, [
            'All Day' => [
                ['Koshari', 'Egypt\'s national dish: lentils, rice, pasta, fried onions, tomato sauce.', 95.00, ['vegan', 'vegetarian']],
                ['Ful Medames', 'Slow-cooked fava beans with cumin, garlic, lemon, olive oil.', 75.00, ['vegan']],
                ['Grilled Kofta Platter', 'Seasoned minced lamb kofta, Egyptian rice, salad, tahini.', 195.00, []],
                ['Macarona Béchamel', 'Egyptian-style pasta gratin with spiced meat and béchamel.', 150.00, []],
            ],
        ]);
    }

    protected function seedMenu(Restaurant $restaurant, array $categories): void
    {
        $menu = Menu::updateOrCreate(
            ['restaurant_id' => $restaurant->id, 'name' => 'Main Menu'],
            ['is_active' => true]
        );

        foreach ($categories as $categoryName => $items) {
            $category = MenuCategory::updateOrCreate(
                ['menu_id' => $menu->id, 'name' => $categoryName],
                ['sort_order' => 0]
            );

            foreach ($items as $i => [$name, $description, $price, $dietary]) {
                MenuItem::updateOrCreate(
                    ['menu_category_id' => $category->id, 'name' => $name],
                    [
                        'description' => $description,
                        'price' => $price,
                        'dietary_info' => $dietary,
                        'is_available' => true,
                        'sort_order' => $i,
                    ]
                );
            }
        }
    }

    protected function seedServices(): void
    {
        $services = [
            [
                'name' => 'Room Service',
                'slug' => 'room-service',
                'desc' => '24-hour in-room dining from the Nile Grill and Saraya Egyptian Kitchen menus.',
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
                'price' => 120.00,
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
                'desc' => 'Cairo tours, Nile dinner cruises, reservations, and local experiences arranged for you.',
                'icon' => 'bell',
                'requestEnabled' => false,
                'price' => null,
                'availability' => ['daily' => '06:00-23:00'],
                'contact' => 'Ext. 0',
            ],
            [
                'name' => 'Airport Transfer',
                'slug' => 'airport-transfer',
                'desc' => 'Private car service to and from Cairo International Airport, booked at least 2 hours ahead.',
                'icon' => 'car',
                'requestEnabled' => true,
                'price' => 450.00,
                'availability' => ['daily' => '00:00-23:59'],
                'contact' => 'Ext. 730',
            ],
            [
                'name' => 'Valet Parking',
                'slug' => 'valet-parking',
                'desc' => 'Valet parking with unlimited in-and-out privileges for the length of stay.',
                'icon' => 'car-front',
                'requestEnabled' => false,
                'price' => 200.00,
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
            Service::updateOrCreate(
                [
                    'hotel_id' => $this->hotel->id,
                    'slug' => $data['slug'],
                ],
                [
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
                ]
            );
        }
    }

    protected function seedEvents(): void
    {
        $events = [
            ['Egyptian Night Buffet', 'egyptian-night-buffet', 'A grand celebration of authentic Egyptian cuisine with live oud music.', 'Saraya Egyptian Kitchen', now()->addDays(3), 'https://images.unsplash.com/photo-1414235077428-338989a2e8c0?w=800'],
            ['Nile Sunset Dinner Cruise', 'nile-sunset-cruise', 'Private felucca dinner cruise along the Nile at golden hour.', 'Nile Grill Pier', now()->addDays(6), 'https://images.unsplash.com/photo-1500375592092-40eb2168fd21?w=800'],
            ['Nile Rooftop Pool Party', 'nile-rooftop-pool-party', 'DJ sets and refreshments at the Nile Rooftop Pool.', 'Nile Rooftop Pool', now()->addDays(9), 'https://images.unsplash.com/photo-1571896349842-33c89424de2d?w=800'],
            ['Morning Yoga & Nile Views', 'morning-yoga-nile', 'Guided rooftop yoga session at sunrise over the Nile.', 'Nile Rooftop Pool', now()->addDays(1), 'https://images.unsplash.com/photo-1544367567-0f2fcb009e0b?w=800'],
            ['Hilton Honors Cairo Mixer', 'hilton-honors-cairo-mixer', 'Cocktails and Egyptian canapés for our Honors members.', 'Executive Lounge', now()->addDays(8), 'https://images.unsplash.com/photo-1445019980597-93fa8acb246c?w=800'],
        ];

        foreach ($events as [$title, $slug, $desc, $location, $date, $image]) {
            $event = Event::updateOrCreate(
                ['hotel_id' => $this->hotel->id, 'slug' => $slug],
                [
                    'hotel_id' => $this->hotel->id,
                    'title' => $title,
                    'slug' => $slug,
                    'description' => $desc,
                    'location' => $location,
                    'start_date' => $date,
                    'start_time' => '19:00:00',
                    'booking_required' => false,
                    'status' => 'published',
                ]
            );
            $this->attachCover($event, $image, $title);
        }
    }

    protected function seedOffers(): void
    {
        $offers = [
            ['Nile Spa Package', 'nile-spa-package', '90-minute Egyptian ritual massage plus full Nile Spa thermal suite access.', 1490.00, 15, true, 'https://images.unsplash.com/photo-1540555700478-4be289fbecef?w=800'],
            ['Nile Dinner for Two', 'nile-dinner-for-two', 'Three-course dinner at Nile Grill with Nile River panoramic seating.', 1200.00, 10, false, 'https://images.unsplash.com/photo-1414235077428-338989a2e8c0?w=800'],
            ['Cairo Weekend Escape', 'cairo-weekend-escape', 'Two-night stay in a Deluxe Nile View Room with daily breakfast and late checkout.', 4800.00, 20, true, 'https://images.unsplash.com/photo-1566073771259-6a8506099945?w=800'],
            ['Family Cairo Package', 'family-cairo-package', 'Superior City View Room, Smarttel Kids Club, and daily breakfast for four.', 6200.00, 12, false, 'https://images.unsplash.com/photo-1602002418082-a4443e081dd1?w=800'],
            ['Pharaohs Discovery Rate', 'pharaohs-discovery-rate', 'Exclusive rate including guided tours to the Egyptian Museum and Old Cairo.', 2100.00, 18, true, 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=800'],
        ];

        foreach ($offers as [$title, $slug, $desc, $price, $discount, $featured, $image]) {
            $offer = Offer::updateOrCreate(
                ['hotel_id' => $this->hotel->id, 'slug' => $slug],
                [
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
                ]
            );
            $this->attachCover($offer, $image, $title);
        }
    }

    protected function seedExperiences(): void
    {
        $experiences = [
            ['Nile Sunrise Yoga', 'nile-sunrise-yoga', 'wellness', 'Morning yoga on the rooftop as the sun rises over the Nile.', '60 minutes', 250.00, true, 'https://images.unsplash.com/photo-1544367567-0f2fcb009e0b?w=800'],
            ['Egyptian Cooking Masterclass', 'egyptian-cooking-masterclass', 'culinary', 'Learn to cook authentic Egyptian dishes with our executive chef — Koshari, Hawawshi, Om Ali.', '2 hours', 650.00, true, 'https://images.unsplash.com/photo-1556910103-1c02745aae4d?w=800'],
            ['Private Nile Felucca Dinner', 'nile-felucca-dinner', 'culinary', 'A private dinner aboard a traditional felucca as the sun sets on the Nile.', '2 hours', 1800.00, true, 'https://images.unsplash.com/photo-1500375592092-40eb2168fd21?w=800'],
            ['Cairo Pharaohs Tour', 'cairo-pharaohs-tour', 'adventure', 'Guided full-day tour of the Egyptian Museum, Old Cairo, and the Citadel of Saladin.', 'Full day', 950.00, true, 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=800'],
            ['Kids Egyptian Adventure Day', 'kids-egyptian-adventure', 'family', 'A full day of Egyptian-themed games, crafts, and storytelling for children.', 'Full day', 0.00, false, 'https://images.unsplash.com/photo-1560184897-ae75f418493e?w=800'],
        ];

        foreach ($experiences as $i => [$title, $slug, $category, $desc, $duration, $price, $featured, $image]) {
            $experience = Experience::updateOrCreate(
                ['hotel_id' => $this->hotel->id, 'slug' => $slug],
                [
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
                ]
            );
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
                'value' => '14:00',
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
            InfoEntry::updateOrCreate(
                [
                    'hotel_id' => $this->hotel->id,
                    'kind' => $entry['kind'],
                    'group' => $entry['group'],
                    'label' => $entry['label'],
                ],
                [
                    'hotel_id' => $this->hotel->id,
                    ...$entry,
                ]
            );
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
