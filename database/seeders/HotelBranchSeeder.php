<?php

namespace Database\Seeders;

use App\Models\Hotel;
use App\Models\HotelBranch;
use Illuminate\Database\Seeder;

class HotelBranchSeeder extends Seeder
{
    public function run(): void
    {
        $hotel = Hotel::first();

        if (! $hotel) {
            return;
        }

        $branches = [
            [
                'slug' => 'smarttel-cairo-garden-city',
                'name' => 'Smarttel Hotel Cairo',
                'domain' => 'cairo.smarttel.com',
                'city' => 'Cairo, Egypt',
                'address' => 'Nile Corniche, Garden City, Cairo, Egypt',
                'phone' => '+20 2 2578 0444',
                'email' => 'cairo@smarttelhotel.com',

                'short_description' => 'A luxurious Nile-front hotel in the heart of Cairo with elegant rooms, premium dining, and panoramic river views.',

                'description' => 'Smarttel Hotel Cairo is a premium five-star destination overlooking the River Nile in the heart of Cairo. The hotel combines contemporary Egyptian hospitality with elegant interiors, spacious rooms and suites, panoramic Nile views, fine dining restaurants, a rooftop pool, a full-service spa, and modern business facilities. Its central location provides convenient access to the Egyptian Museum, Tahrir Square, Cairo Tower, and the city center.',

                'cover_image_url' => asset('images/hotel/ramses-hilton/exterior-night-shot.png'),

                'gallery_urls' => [
                    asset('images/hotel/ramses-hilton/exterior-night-shot.png'),
                    asset('images/hotel/ramses-hilton/duplex-suite-living-area-.png'),
                    asset('images/hotel/ramses-hilton/executive-king-room-nile-view-.png'),
                    asset('images/hotel/ramses-hilton/ramses-hilton-terrace-cafe-day.png'),
                    asset('images/hotel/ramses-hilton/cairh-executive-renovated-floor-kids-area-no.11.png'),
                    asset('images/hotel/ramses-hilton/cairh-fitness-center-.png'),
                    asset('images/hotel/ramses-hilton/ramses-hilton-king-nile-deluxe.png'),
                ],

                'latitude' => 30.0444,
                'longitude' => 31.2357,

                'features' => [
                    'Nile River View',
                    'Rooftop Pool',
                    'Full-Service Spa',
                    'Fine Dining Restaurants',
                    'Fitness Center',
                    'Business Center',
                    'Meeting Rooms',
                    'Valet Parking',
                    '24/7 Front Desk',
                    'Room Service',
                ],

                'is_main' => true,
                'sort_order' => 1,

                'ar' => [
                    'name' => 'فندق سمارتيل القاهرة',
                    'city' => 'القاهرة، مصر',
                    'address' => 'كورنيش النيل، جاردن سيتي، القاهرة، مصر',

                    'short_description' => 'فندق فاخر على كورنيش النيل في قلب القاهرة، يوفر غرفاً أنيقة ومطاعم متميزة وإطلالات بانورامية على النيل.',

                    'description' => 'يقدم فندق سمارتيل القاهرة تجربة إقامة فاخرة على ضفاف نهر النيل في قلب القاهرة. يجمع الفندق بين الضيافة المصرية العصرية والتصميم الأنيق، ويوفر غرفاً وأجنحة واسعة، وإطلالات مميزة على النيل، ومطاعم راقية، ومسبحاً على السطح، وسبا متكاملاً، ومرافق حديثة لرجال الأعمال. ويتميز بموقع مركزي بالقرب من المتحف المصري وميدان التحرير وبرج القاهرة ووسط المدينة.',
                ],
            ],

            [
                'slug' => 'smarttel-alexandria-corniche',
                'name' => 'Smarttel Hotel Alexandria',
                'domain' => 'alexandria.smarttel.com',
                'city' => 'Alexandria, Egypt',
                'address' => 'Alexandria Corniche, Sidi Bishr, Alexandria, Egypt',
                'phone' => '+20 3 547 7799',
                'email' => 'alexandria@smarttelhotel.com',

                'short_description' => 'A stylish Mediterranean beachfront hotel offering sea views, resort amenities, and easy access to Alexandria attractions.',

                'description' => 'Smarttel Hotel Alexandria is a modern Mediterranean beachfront property designed for leisure and business travelers. Guests can enjoy comfortable rooms with sea views, an outdoor swimming pool, a private beach experience, a fitness center, relaxing spa facilities, and a selection of restaurants and cafés. The hotel is ideally positioned for exploring Alexandria Corniche, the Bibliotheca Alexandrina, historic landmarks, and the city center.',

                'cover_image_url' => asset('images/hotel/alexandria-corniche/Hilton-Alexandria-Corniche-Exterior-View.png'),

                'gallery_urls' => [
                    asset('images/hotel/alexandria-corniche/Hilton-Alexandria-Corniche-Exterior-View.png'),
                    asset('images/hotel/alexandria-corniche/img-2730.png'),
                    asset('images/hotel/alexandria-corniche/img-2469.png'),
                    asset('images/hotel/alexandria-corniche/Deluxe-Room-Twin-SeaView.png'),
                    asset('images/hotel/alexandria-corniche/alyac-santorini-greek-restaurant.png'),
                    asset('images/hotel/alexandria-corniche/img-3282.png'),
                    asset('images/hotel/alexandria-corniche/daytime.png'),
                ],

                'latitude' => 31.2001,
                'longitude' => 29.9187,

                'features' => [
                    'Mediterranean Sea View',
                    'Private Beach',
                    'Outdoor Pool',
                    'Fitness Center',
                    'Spa',
                    'Seafood Restaurant',
                    'Café',
                    'Meeting Rooms',
                    'Kids Area',
                    '24/7 Front Desk',
                ],

                'is_main' => false,
                'sort_order' => 2,

                'ar' => [
                    'name' => 'فندق سمارتيل الإسكندرية',
                    'city' => 'الإسكندرية، مصر',
                    'address' => 'كورنيش الإسكندرية، سيدي بشر، الإسكندرية، مصر',

                    'short_description' => 'فندق عصري على البحر المتوسط يوفر إطلالات بحرية ومرافق منتجعية وموقعاً مميزاً بالقرب من معالم الإسكندرية.',

                    'description' => 'يقدم فندق سمارتيل الإسكندرية تجربة إقامة عصرية على البحر المتوسط، مصممة للمسافرين بغرض الترفيه والأعمال. يوفر الفندق غرفاً مريحة بإطلالات بحرية، ومسبحاً خارجياً، وتجربة شاطئية خاصة، ومركزاً للياقة البدنية، ومرافق سبا للاسترخاء، بالإضافة إلى مجموعة من المطاعم والمقاهي. ويتميز بموقع مناسب لاستكشاف كورنيش الإسكندرية ومكتبة الإسكندرية والمعالم التاريخية ووسط المدينة.',
                ],
            ],

            [
                'slug' => 'smarttel-sharm-el-sheikh',
                'name' => 'Smarttel Resort Sharm El Sheikh',
                'domain' => 'sharm.smarttel.com',
                'city' => 'Sharm El Sheikh, Egypt',
                'address' => 'Naama Bay, Sharm El Sheikh, South Sinai, Egypt',
                'phone' => '+20 69 360 0136',
                'email' => 'sharm@smarttelhotel.com',

                'short_description' => 'A premium Red Sea resort featuring private beach access, multiple pools, water activities, and family-friendly facilities.',

                'description' => 'Smarttel Resort Sharm El Sheikh is a vibrant Red Sea resort offering an unforgettable coastal escape. Located near Naama Bay, the resort features comfortable rooms and suites, multiple swimming pools, private beach access, snorkeling and diving experiences, tennis facilities, kids activities, and a variety of restaurants and beach bars. It is an ideal destination for families, couples, and travelers looking for a relaxing resort experience.',

                'cover_image_url' => asset('images/hotel/sharm-el-sheikh/Hero-Photo.png'),

                'gallery_urls' => [
                    asset('images/hotel/sharm-el-sheikh/img-3870.png'),
                    asset('images/hotel/sharm-el-sheikh/img-3682.png'),
                    asset('images/hotel/sharm-el-sheikh/img-4576.png'),
                    asset('images/hotel/sharm-el-sheikh/img-4464.png'),
                    asset('images/hotel/sharm-el-sheikh/img-4659.png'),
                    asset('images/hotel/sharm-el-sheikh/img-4004.png'),
                    asset('images/hotel/sharm-el-sheikh/img-5016.png'),
                    asset('images/hotel/sharm-el-sheikh/img-3889.png'),
                ],

                'latitude' => 27.9158,
                'longitude' => 34.3300,

                'features' => [
                    'Private Red Sea Beach',
                    'Multiple Swimming Pools',
                    'Snorkeling',
                    'Diving Center',
                    'Water Sports',
                    'Tennis Courts',
                    'Kids Club',
                    'Spa',
                    'Beach Bar',
                    'International Restaurants',
                ],

                'is_main' => false,
                'sort_order' => 3,

                'ar' => [
                    'name' => 'منتجع سمارتيل شرم الشيخ',
                    'city' => 'شرم الشيخ، مصر',
                    'address' => 'خليج نعمة، شرم الشيخ، جنوب سيناء، مصر',

                    'short_description' => 'منتجع فاخر على البحر الأحمر يوفر شاطئاً خاصاً ومسابح متعددة وأنشطة مائية ومرافق مناسبة للعائلات.',

                    'description' => 'يوفر منتجع سمارتيل شرم الشيخ تجربة ساحلية مميزة على البحر الأحمر بالقرب من خليج نعمة. يضم المنتجع غرفاً وأجنحة مريحة، ومسابح متعددة، وشاطئاً خاصاً، وتجارب للغطس والسنوركلينج، وملاعب للتنس، وأنشطة للأطفال، بالإضافة إلى مجموعة متنوعة من المطاعم والبارات الشاطئية. ويعد المنتجع خياراً مناسباً للعائلات والأزواج ومحبي الاسترخاء والأنشطة البحرية.',
                ],
            ],

            [
                'slug' => 'smarttel-hurghada-red-sea',
                'name' => 'Smarttel Resort Hurghada',
                'city' => 'Hurghada, Egypt',
                'address' => 'Red Sea Coast, Hurghada, Red Sea Governorate, Egypt',
                'phone' => '+20 65 344 5999',
                'email' => 'hurghada@smarttelhotel.com',

                'short_description' => 'A beachfront Red Sea resort with private beach access, pools, diving, watersports, and family activities.',

                'description' => 'Smarttel Resort Hurghada offers a complete Red Sea holiday experience with direct access to the beach and a wide range of leisure facilities. Guests can enjoy spacious rooms and suites, swimming pools, a private sandy beach, diving and snorkeling activities, water sports, kids facilities, restaurants, and relaxing outdoor spaces. The resort is designed for both family holidays and extended leisure stays.',

                'cover_image_url' => asset('images/hotel/hurghada-red-sea/Hero-Picture.png'),

                'gallery_urls' => [
                    asset('images/hotel/hurghada-red-sea/hrghi-1889.png'),
                    asset('images/hotel/hurghada-red-sea/hrghi-k1tv-bedroom.png'),
                    asset('images/hotel/hurghada-red-sea/hrghi-2434.png'),
                    asset('images/hotel/hurghada-red-sea/hrghi-2511.png'),
                    asset('images/hotel/hurghada-red-sea/hrghi-2585.png'),
                    asset('images/hotel/hurghada-red-sea/hrghi-1487.png'),
                    asset('images/hotel/hurghada-red-sea/hrghi-1362.png'),
                ],

                'latitude' => 27.2579,
                'longitude' => 33.8116,

                'features' => [
                    'Red Sea Beachfront',
                    'Private Beach',
                    'Swimming Pools',
                    'Diving Center',
                    'Snorkeling',
                    'Water Sports',
                    'Kids Club',
                    'Beach Restaurant',
                    'Spa',
                    'All-Inclusive Option',
                ],

                'is_main' => false,
                'sort_order' => 4,

                'ar' => [
                    'name' => 'منتجع سمارتيل الغردقة',
                    'city' => 'الغردقة، مصر',
                    'address' => 'ساحل البحر الأحمر، الغردقة، محافظة البحر الأحمر، مصر',

                    'short_description' => 'منتجع شاطئي على البحر الأحمر يوفر شاطئاً خاصاً ومسابح وأنشطة للغوص والرياضات المائية ومرافق للعائلات.',

                    'description' => 'يقدم منتجع سمارتيل الغردقة تجربة متكاملة للعطلات على البحر الأحمر مع وصول مباشر إلى الشاطئ ومجموعة واسعة من المرافق الترفيهية. يمكن للضيوف الاستمتاع بغرف وأجنحة واسعة، ومسابح، وشاطئ رملي خاص، وأنشطة الغوص والسنوركلينج، والرياضات المائية، ومرافق للأطفال، بالإضافة إلى المطاعم والمساحات الخارجية المخصصة للاسترخاء. صُمم المنتجع ليناسب العائلات والإقامات الترفيهية الطويلة.',
                ],
            ],

            [
                'slug' => 'smarttel-luxor-nile-valley',
                'name' => 'Smarttel Hotel Luxor',
                'city' => 'Luxor, Egypt',
                'address' => 'Khalid Ibn El Walid Street, Luxor, Egypt',
                'phone' => '+20 95 238 0422',
                'email' => 'luxor@smarttelhotel.com',

                'short_description' => 'A Nile-side hotel combining comfortable accommodation, Egyptian heritage, and easy access to Luxor\'s historic landmarks.',

                'description' => 'Smarttel Hotel Luxor offers a relaxing Nile-side stay in one of Egypt\'s most historic destinations. The hotel features comfortable rooms, Nile and city views, an outdoor swimming pool, restaurants serving local and international cuisine, wellness facilities, and concierge services. Guests can easily explore the Valley of the Kings, Karnak Temple, Luxor Temple, museums, and traditional Nile experiences.',

                'cover_image_url' => asset('images/hotel/luxor-nile-valley/Hero-Photo.png'),

                'gallery_urls' => [
                    asset('images/hotel/luxor-nile-valley/luxhitw-spa-suite.png'),
                    asset('images/hotel/luxor-nile-valley/luxhitw-jannah-restaurant.png'),
                    asset('images/hotel/luxor-nile-valley/luxhitw-olives-restaurant.png'),
                    asset('images/hotel/luxor-nile-valley/luxhitw-fitness-centre.png'),
                    asset('images/hotel/luxor-nile-valley/luxhitw-ballroom.png'),
                    asset('images/hotel/luxor-nile-valley/luxhitw-hilton-meeting-room.png'),
                    asset('images/hotel/luxor-nile-valley/luxhitw-lobby.png'),
                ],

                'latitude' => 25.6872,
                'longitude' => 32.6396,

                'features' => [
                    'Nile Views',
                    'Outdoor Pool',
                    'Egyptian Restaurant',
                    'International Restaurant',
                    'Wellness Facilities',
                    'Tour Desk',
                    'Valley of the Kings Tours',
                    'Karnak Temple Tours',
                    'Felucca Experiences',
                    'Airport Transfer',
                ],

                'is_main' => false,
                'sort_order' => 5,

                'ar' => [
                    'name' => 'فندق سمارتيل الأقصر',
                    'city' => 'الأقصر، مصر',
                    'address' => 'شارع خالد بن الوليد، الأقصر، مصر',

                    'short_description' => 'فندق على النيل يجمع بين الإقامة المريحة والتراث المصري والموقع المميز بالقرب من أهم معالم الأقصر.',

                    'description' => 'يوفر فندق سمارتيل الأقصر إقامة مريحة على ضفاف النيل في واحدة من أكثر الوجهات التاريخية شهرة في مصر. يضم الفندق غرفاً مريحة وإطلالات على النيل والمدينة ومسبحاً خارجياً ومطاعم تقدم المأكولات المصرية والعالمية ومرافق للاسترخاء وخدمات الكونسيرج. ويمكن للضيوف بسهولة استكشاف وادي الملوك ومعبد الكرنك ومعبد الأقصر والمتاحف وتجارب النيل التقليدية.',
                ],
            ],
        ];

        foreach ($branches as $data) {
            $ar = $data['ar'];

            unset($data['ar']);

            $branch = HotelBranch::updateOrCreate(
                [
                    'hotel_id' => $hotel->id,
                    'slug' => $data['slug'],
                ],
                $data
            );

            $branch->setTranslations('ar', $ar);
            $branch->save();
        }
    }
}
