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
                'slug' => 'horizon-bay-resort',
                'name' => 'Horizon Bay Resort & Marina',
                'city' => 'Horizon Bay',
                'address' => '1 Horizon Bay Drive, Coastal Boulevard',
                'phone' => '+1 555 010 2020',
                'email' => 'horizonbay@hiltongrandhorizon.example',
                'short_description' => 'Iconic beachfront resort with private white-sand beach and private yacht marina.',
                'description' => 'Located directly on the azure waters of the bay, our flagship beachfront resort features secluded cabanas, motorized watersports, five signature dining venues, and panoramic sunset views over the ocean.',
                'cover_image_url' => 'https://images.unsplash.com/photo-1566073771259-6a8506099945?w=1200',
                'gallery_urls' => [
                    'https://images.unsplash.com/photo-1582719508461-905c673771fd?w=1200',
                    'https://images.unsplash.com/photo-1540555700478-4be289fbecef?w=1200',
                    'https://images.unsplash.com/photo-1571896349842-33c89424de2d?w=1200',
                    'https://images.unsplash.com/photo-1520250497591-112f2f40a3f4?w=1200',
                ],
                'latitude' => 25.2048,
                'longitude' => 55.2708,
                'features' => ['Private Beach', 'Yacht Marina', 'Infinity Pool', 'Luxury Spa', 'Valet Parking', 'Concierge 24/7'],
                'is_main' => true,
                'sort_order' => 1,
                'ar' => [
                    'name' => 'منتجع ومارينا هورايزون باي',
                    'city' => 'هورايزون باي',
                    'address' => '١ طريق هورايزون باي، شارع الساحل',
                    'short_description' => 'منتجع شاطئي فاخر مع شاطئ رملي خاص ومارينا لليخوت.',
                    'description' => 'يقع منتجعنا الرئيسي مباشرة على مياه الخليج الفيروزية، ويضم كبائن خاصة، ورياضات مائية، وخمسة مطاعم مميزة، وإطلالات بانورامية خلابة على الغروب.',
                ],
            ],
            [
                'slug' => 'downtown-towers',
                'name' => 'Grand Horizon Downtown Towers',
                'city' => 'Downtown Financial District',
                'address' => '450 Financial Avenue, Skyline District',
                'phone' => '+1 555 010 3030',
                'email' => 'downtown@hiltongrandhorizon.example',
                'short_description' => 'Cosmopolitan luxury living in the vibrant heart of the city.',
                'description' => 'Rising above the bustling metropolis, Grand Horizon Downtown offers sky-high luxury with a rooftop infinity pool, Michelin-inspired dining, direct mall access, and state-of-the-art conference facilities.',
                'cover_image_url' => 'https://images.unsplash.com/photo-1542314831-068cd1dbfeeb?w=1200',
                'gallery_urls' => [
                    'https://images.unsplash.com/photo-1551882547-ff40c63fe5fa?w=1200',
                    'https://images.unsplash.com/photo-1578683010236-d716f9a3f461?w=1200',
                    'https://images.unsplash.com/photo-1566073771259-6a8506099945?w=1200',
                    'https://images.unsplash.com/photo-1582719508461-905c673771fd?w=1200',
                ],
                'latitude' => 25.1972,
                'longitude' => 55.2744,
                'features' => ['Rooftop Sky Pool', 'Direct Metro Access', 'Business Center', 'Fine Dining', 'Helipad', 'Fitness Club'],
                'is_main' => false,
                'sort_order' => 2,
                'ar' => [
                    'name' => 'أبراج جراند هورايزون داون تاون',
                    'city' => 'وسط المدينة - الحي المالي',
                    'address' => '٤٥٠ الجادة المالية، حي الأفق المالي',
                    'short_description' => 'إقامة عصرية فاخرة في قلب الحي المالي والثقافي النابض للمدينة.',
                    'description' => 'تتربع أبراج جراند هورايزون وسط المدينة لتقدم فخامة استثنائية مع مسبح لا متناهي على السطح، ومطاعم عالمية، ومدخل مباشر لمراكز التسوق، وقاعات مؤتمرات متطورة.',
                ],
            ],
            [
                'slug' => 'palm-island-oasis',
                'name' => 'Grand Horizon Palm Oasis Retreat',
                'city' => 'Palm Jumeirah',
                'address' => 'Crescent West 12, The Palm Archipelago',
                'phone' => '+1 555 010 4040',
                'email' => 'palm@hiltongrandhorizon.example',
                'short_description' => 'Exclusive private island sanctuary with overwater villas and tranquil coral lagoons.',
                'description' => 'An exclusive retreat designed for serenity and relaxation. Immerse yourself in secluded overwater bungalows, private plunge pools, holistic wellness therapies, and private boat transfers.',
                'cover_image_url' => 'https://images.unsplash.com/photo-1520250497591-112f2f40a3f4?w=1200',
                'gallery_urls' => [
                    'https://images.unsplash.com/photo-1571003123894-1f0594d2b5d9?w=1200',
                    'https://images.unsplash.com/photo-1540541338287-41700207dee6?w=1200',
                    'https://images.unsplash.com/photo-1618773928121-c32242e63f39?w=1200',
                    'https://images.unsplash.com/photo-1571896349842-33c89424de2d?w=1200',
                ],
                'latitude' => 25.1124,
                'longitude' => 55.1390,
                'features' => ['Overwater Villas', 'Private Lagoon', 'Holistic Spa', 'Private Boat Transfer', 'Butler Service', 'Coral Diving'],
                'is_main' => false,
                'sort_order' => 3,
                'ar' => [
                    'name' => 'جراند هورايزون واحة النخلة',
                    'city' => 'نخلة جميرا',
                    'address' => 'الهلال الغربي ١٢، أرخبيل النخلة',
                    'short_description' => 'ملاذ حصري في جزيرة خاصة مع فلل فوق الماء وبحيرات مرجانية هادئة.',
                    'description' => 'ملاذ مصمم للسكينة والاسترخاء التام. استمتع بأكواخ شاطئية مع مسابح خاصة، وعلاجات سبا شمولية، وخدمة نقل بالقوارب الخاصة.',
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
        }
    }
}
