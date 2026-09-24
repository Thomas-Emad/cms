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
                'slug' => 'hilton-dubai-palm-jumeirah',
                'name' => 'Hilton Dubai Palm Jumeirah',
                'city' => 'Palm Jumeirah, Dubai',
                'address' => 'Palm West Beach, The Palm Jumeirah, Dubai, United Arab Emirates',
                'phone' => '+971 4 230 0000',
                'email' => 'palmjumeirah.info@hilton.com',
                'short_description' => 'Flagship 5-star beachfront resort situated directly on vibrant Palm West Beach.',
                'description' => 'Located on the iconic Palm West Beach, Hilton Dubai Palm Jumeirah features 605 luxury rooms and suites, 10 celebrated restaurants and bars including Barfly by Buddha-Bar, a 65-meter infinity pool, eforea Spa, and panoramic views of Dubai Marina and the Arabian Gulf.',
                'cover_image_url' => 'https://images.unsplash.com/photo-1566073771259-6a8506099945?w=1200',
                'gallery_urls' => [
                    'https://images.unsplash.com/photo-1582719508461-905c673771fd?w=1200',
                    'https://images.unsplash.com/photo-1540555700478-4be289fbecef?w=1200',
                    'https://images.unsplash.com/photo-1571896349842-33c89424de2d?w=1200',
                    'https://images.unsplash.com/photo-1520250497591-112f2f40a3f4?w=1200',
                ],
                'latitude' => 25.1124,
                'longitude' => 55.1389,
                'features' => ['Private Beach', '65m Infinity Pool', 'eforea Spa', '10 Dining Venues', 'Executive Lounge', 'Valet Parking'],
                'is_main' => true,
                'sort_order' => 1,
                'ar' => [
                    'name' => 'فندق ومنتجع هيلتون دبي نخلة جميرا',
                    'city' => 'نخلة جميرا، دبي',
                    'address' => 'بالم ويست بيتش، نخلة جميرا، دبي، الإمارات العربية المتحدة',
                    'short_description' => 'المنتجع الرئيسي الفاخر 5 نجوم الواقع مباشرة على شاطئ بالم ويست بيتش.',
                    'description' => 'يقع هيلتون دبي نخلة جميرا على شاطئ بالم ويست بيتش الأيقوني، ويضم 605 غرفة وجناحاً فاخراً، و10 مطاعم واستراحات عالمية شهيرة مثل بارفلاي من بوذا بار، ومسبحاً لا متناهياً بطول 65 متراً، وسبا إيفوريا، وإطلالات بانورامية على مرسى دبي والخليج العربي.',
                ],
            ],
            [
                'slug' => 'hilton-dubai-jumeirah-the-walk',
                'name' => 'Hilton Dubai Jumeirah (The Walk, JBR)',
                'city' => 'JBR & Dubai Marina',
                'address' => 'The Walk, Jumeirah Beach Residence, Dubai Marina, Dubai',
                'phone' => '+971 4 318 2999',
                'email' => 'dubaijumeirah.info@hilton.com',
                'short_description' => 'Vibrant beachfront hotel in the heart of JBR The Walk shopping & dining strip.',
                'description' => 'Set on the famous promenade of The Walk at JBR, this resort offers private beach access, lush gardens, watersports, award-winning Italian dining at BiCE Ristorante, and pedestrian access to Dubai Marina.',
                'cover_image_url' => 'https://images.unsplash.com/photo-1542314831-068cd1dbfeeb?w=1200',
                'gallery_urls' => [
                    'https://images.unsplash.com/photo-1551882547-ff40c63fe5fa?w=1200',
                    'https://images.unsplash.com/photo-1578683010236-d716f9a3f461?w=1200',
                    'https://images.unsplash.com/photo-1566073771259-6a8506099945?w=1200',
                    'https://images.unsplash.com/photo-1582719508461-905c673771fd?w=1200',
                ],
                'latitude' => 25.0784,
                'longitude' => 55.1328,
                'features' => ['Direct JBR Walk Access', 'BiCE Italian Dining', 'Private Beach Club', 'Outdoor Shaded Pool', 'Watersports'],
                'is_main' => false,
                'sort_order' => 2,
                'ar' => [
                    'name' => 'هيلتون دبي جميرا (ذا ووك - جي بي آر)',
                    'city' => 'جي بي آر ومرسى دبي',
                    'address' => 'ممشى ذا ووك، مساكن شاطئ جميرا، مرسى دبي، دبي',
                    'short_description' => 'فندق شاطئي حيوي في قلب ممشى ذا ووك الشهير للتسوق والمطاعم.',
                    'description' => 'يقع هذا المنتجع على ممشى ذا ووك في جي بي آر، ويوفر إمكانية الوصول إلى شاطئ خاص وحدائق غناء ورياضات مائية ومطعم بيتشي الإيطالي الحائز على جوائز، وسهولة الوصول سيراً إلى دبي مارينا.',
                ],
            ],
            [
                'slug' => 'waldorf-astoria-dubai-palm-jumeirah',
                'name' => 'Waldorf Astoria Dubai Palm Jumeirah',
                'city' => 'Palm Jumeirah East Crescent',
                'address' => 'Crescent Road, The Palm Jumeirah, Dubai',
                'phone' => '+971 4 818 2222',
                'email' => 'waldorfastoria.palmjumeirah@hilton.com',
                'short_description' => 'Palatial luxury haven with 200 meters of private soft white sand beach.',
                'description' => 'The crown jewel of Hilton luxury, Waldorf Astoria Dubai Palm Jumeirah features palatial architecture, bespoke Personal Concierge service, Michelin-starred culinary artistry, two temperature-controlled swimming pools, and extensive spa sanctuary.',
                'cover_image_url' => 'https://images.unsplash.com/photo-1520250497591-112f2f40a3f4?w=1200',
                'gallery_urls' => [
                    'https://images.unsplash.com/photo-1571003123894-1f0594d2b5d9?w=1200',
                    'https://images.unsplash.com/photo-1540541338287-41700207dee6?w=1200',
                    'https://images.unsplash.com/photo-1618773928121-c32242e63f39?w=1200',
                    'https://images.unsplash.com/photo-1571896349842-33c89424de2d?w=1200',
                ],
                'latitude' => 25.1325,
                'longitude' => 55.1528,
                'features' => ['200m Private Beach', 'Personal Concierge', 'Waldorf Astoria Spa', '3-Star Michelin Chef Venues', 'Helipad'],
                'is_main' => false,
                'sort_order' => 3,
                'ar' => [
                    'name' => 'والدورف أستوريا دبي نخلة جميرا',
                    'city' => 'الهلال الشرقي، نخلة جميرا',
                    'address' => 'طريق الهلال، نخلة جميرا، دبي',
                    'short_description' => 'ملاذ فاخر بطراز قصر ملكي مع شاطئ رملي أبيض خاص بطول 200 متر.',
                    'description' => 'درة تاج الفخامة في مجموعة هيلتون، يتميز والدورف أستوريا بتصميمه المعماري الفخم، وخدمة المساعد الشخصي، وتجارب الطهي الراقية، ومسبحين بدرجات حرارة مضبوطة، وسبا علاجي استثنائي.',
                ],
            ],
            [
                'slug' => 'conrad-dubai-financial-district',
                'name' => 'Conrad Dubai (Sheikh Zayed Road)',
                'city' => 'Sheikh Zayed Road, Financial Center',
                'address' => 'Sheikh Zayed Road, Trade Centre 1, Dubai',
                'phone' => '+971 4 444 7444',
                'email' => 'conrad.dubai@hilton.com',
                'short_description' => 'Ultra-chic modern sanctuary in the central financial & business epicentre.',
                'description' => 'Rising majestically in Dubai financial district, Conrad Dubai blends urban sophistication with a 5,500 sqm tropical pool deck oasis, signature Purobeach lounge, luxury spa, and direct metro link to Dubai World Trade Centre.',
                'cover_image_url' => 'https://images.unsplash.com/photo-1551882547-ff40c63fe5fa?w=1200',
                'gallery_urls' => [
                    'https://images.unsplash.com/photo-1542314831-068cd1dbfeeb?w=1200',
                    'https://images.unsplash.com/photo-1578683010236-d716f9a3f461?w=1200',
                    'https://images.unsplash.com/photo-1590490360182-c33d57733427?w=1200',
                ],
                'latitude' => 25.2265,
                'longitude' => 55.2831,
                'features' => ['5,500 sqm Urban Pool Deck', 'Direct Metro Link', 'Conrad Spa', 'Purobeach Urban Oasis', 'Ballroom'],
                'is_main' => false,
                'sort_order' => 4,
                'ar' => [
                    'name' => 'كونراد دبي (شارع الشيخ زايد)',
                    'city' => 'شارع الشيخ زايد، مركز التجارة العالمي',
                    'address' => 'شارع الشيخ زايد، مركز التجارة الأول، دبي',
                    'short_description' => 'ملاذ حضري فائق الرقي في قلب المركز المالي والتجاري لدبي.',
                    'description' => 'يرتفع كونراد دبي في قلب الحي المالي ليجمع بين الطابع العصري وواحة المسبح الاستوائية على مساحة 5,500 متر مربع، وسبا فاخر، مع اتصال مباشر بمحطة المترو ومركز دبي التجاري العالمي.',
                ],
            ],
            [
                'slug' => 'hilton-abu-dhabi-yas-island',
                'name' => 'Hilton Abu Dhabi Yas Island',
                'city' => 'Yas Island, Abu Dhabi',
                'address' => 'Yas Bay Waterfront, Yas Island, Abu Dhabi, United Arab Emirates',
                'phone' => '+971 2 208 6888',
                'email' => 'abudhabi.yasisland@hilton.com',
                'short_description' => 'Vibrant waterfront resort located on Yas Bay with access to world-class theme parks.',
                'description' => 'Nestled on the dynamic Yas Bay Waterfront, Hilton Abu Dhabi Yas Island provides an exceptional retreat with outdoor infinity pools, access to Ferrari World, Warner Bros. World, and Yas Waterworld, with magnificent views of the Arabian Gulf.',
                'cover_image_url' => 'https://images.unsplash.com/photo-1571896349842-33c89424de2d?w=1200',
                'gallery_urls' => [
                    'https://images.unsplash.com/photo-1566073771259-6a8506099945?w=1200',
                    'https://images.unsplash.com/photo-1582719508461-905c673771fd?w=1200',
                ],
                'latitude' => 24.4672,
                'longitude' => 54.6035,
                'features' => ['Yas Bay Waterfront', 'Theme Park Access', 'Beach Club', 'Waterfront Infinity Pool', 'eforea Spa'],
                'is_main' => false,
                'sort_order' => 5,
                'ar' => [
                    'name' => 'هيلتون أبوظبي جزيرة ياس',
                    'city' => 'جزيرة ياس، أبوظبي',
                    'address' => 'واجهة ياس باي البحرية، جزيرة ياس، أبوظبي، الإمارات العربية المتحدة',
                    'short_description' => 'منتجع مائي مميز على واجهة ياس باي مع دخول لأشهر مدن الألعاب العالمية.',
                    'description' => 'يقع فندق هيلتون أبوظبي على واجهة ياس باي البحرية، مقدماً إقامة استثنائية مع مسابح لا متناهية وتذاكر لمدن الألعاب الترفيهية عالمية المستوى في جزيرة ياس وإطلالات رائعة.',
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
