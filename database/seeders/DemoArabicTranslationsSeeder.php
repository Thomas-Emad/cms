<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\Experience;
use App\Models\Facility;
use App\Models\Hotel;
use App\Models\InfoEntry;
use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Models\Offer;
use App\Models\Page;
use App\Models\Restaurant;
use App\Models\Room;
use App\Models\Service;
use Illuminate\Database\Seeder;

class DemoArabicTranslationsSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedHotel();
        $this->seedFacilities();
        $this->seedRestaurants();
        $this->seedMenus();
        $this->seedRooms();
        $this->seedServices();
        $this->seedEvents();
        $this->seedOffers();
        $this->seedExperiences();
        $this->seedInfoEntries();
        $this->seedPages();
    }

    protected function seedFacilities(): void
    {
        $facilities = [
            'nile-conference-suite' => [
                'name' => 'جناح مؤتمرات النيل',
                'description' => 'قاعة مؤتمرات تنفيذية متطورة مجهزة بأحدث تقنيات العرض الصوتي والمرئي، تطل على نهر النيل، مثالية للاجتماعات الكبرى والندوات الدولية.',
                'short_description' => 'قاعة مؤتمرات حديثة تسع حتى 120 شخصاً مع إطلالة بانورامية على النيل.',
                'building' => 'المبنى الرئيسي',
                'floor' => 'الطابق الثاني',
                'wing' => 'الجناح الشرقي',
            ],
            'nile-spa-wellness' => [
                'name' => 'سبا النيل وعافية الروح',
                'description' => 'ملاذ متكامل للاسترخاء مستوحى من طقوس الجمال المصرية القديمة، يقدم جلسات علاجية مستوحاة من تقاليد الملكة كليوباترا باستخدام منتجات البردي والمكونات الطبيعية.',
                'short_description' => 'علاجات تدليك وجلسات استرخاء متكاملة وسط أجواء هادئة مطلة على النيل.',
                'building' => 'مبنى العافية',
                'floor' => 'الطابق الأرضي',
                'wing' => 'جناح السبا',
            ],
            'executive-business-center' => [
                'name' => 'مركز الأعمال التنفيذي',
                'description' => 'بيئة عمل هادئة ومجهزة بكافة التجهيزات المكتبية وخدمات السكرتارية وخطوط اتصال فائقة السرعة لدعم أعمالك في قلب القاهرة.',
                'short_description' => 'محطات عمل مجهزة وخدمات طباعة ومكاتب خاصة لرجال الأعمال على مدار الساعة.',
                'building' => 'المبنى الرئيسي',
                'floor' => 'الطابق الأول',
                'wing' => 'الجناح الشرقي',
            ],
            'executive-lounge' => [
                'name' => 'صالون التنفيذيين',
                'description' => 'ملاذ راقٍ في الطابق الأعلى يوفر إفطاراً مجانياً ومرطبات طوال اليوم وسناكات مصرية في المساء، حصرياً لضيوف الأجنحة التنفيذية.',
                'short_description' => 'إفطار مجاني وسناكات مسائية مع إطلالة على النيل لضيوف الأجنحة التنفيذية.',
                'building' => 'المبنى الرئيسي',
                'floor' => 'الطابق 14',
                'wing' => 'البرج',
            ],
            'grand-nile-ballroom' => [
                'name' => 'قاعة جراند النيل الكبرى',
                'description' => 'قاعة احتفالات فخمة مطلة على النيل ذات أسقف عالية وثريات كريستالية مبهرة، مصممة لاستضافة أفخم حفلات الزفاف والمؤتمرات الكبرى في القاهرة.',
                'short_description' => 'قاعة احتفالات فاخرة تتسع لما يصل إلى 600 ضيف بتصميم معماري ملكي أخاذ.',
                'building' => 'مركز المؤتمرات',
                'floor' => 'الطابق الأرضي',
                'wing' => 'الجناح الشمالي',
            ],
            'smarttel-kids-club' => [
                'name' => 'نادي أطفال سمارتيل',
                'description' => 'عالم من المرح والأنشطة التعليمية المصرية الممتعة بإشراف فريق محترف متخصص لضمان أوقات ممتعة وآمنة لأطفالكم.',
                'short_description' => 'أنشطة ترفيهية وحرف مصرية وألعاب تفاعلية بإشراف متخصص للأطفال من سن 4 إلى 12 عاماً.',
                'building' => 'مبنى الأنشطة',
                'floor' => 'الطابق الأرضي',
                'wing' => 'حديقة الأطفال',
            ],
            'nile-boardroom' => [
                'name' => 'قاعة اجتماعات النيل التنفيذية',
                'description' => 'غرفة اجتماعات حصرية لكبار المسؤولين التنفيذيين مزودة بشاشات تفاعلية ذكية ونظام اتصال فيديو فائق الدقة مع إطلالة على النيل.',
                'short_description' => 'قاعة مخصصة لاجتماعات الإدارة العليا بكامل التجهيزات الذكية وإطلالة على النيل.',
                'building' => 'المبنى الرئيسي',
                'floor' => 'الطابق الثاني',
                'wing' => 'الجناح الغربي',
            ],
            'nile-rooftop-pool' => [
                'name' => 'مسبح سطح النيل',
                'description' => 'مسبح لامتناهٍ دافئ على السطح يوفر إطلالات خلابة على نهر النيل وأفق القاهرة بزاوية 360 درجة، مع لاونج مخصص وخدمة مشروبات.',
                'short_description' => 'مسبح إنفينيتي ساحر على السطح مع كراسي استرخاء وإطلالات مذهلة على النيل.',
                'building' => 'برج الفندق',
                'floor' => 'السطح',
                'wing' => 'السطح',
            ],
            'pharaoh-fitness-club' => [
                'name' => 'نادي الفرعون للياقة البدنية',
                'description' => 'مركز لياقة بدنية متطور مفتوح على مدار الساعة مجهز بأحدث أجهزة تكنوجيم وصالة يوجا مطلة على النيل ومدربين محترفين.',
                'short_description' => 'أحدث أجهزة الكارديو والأثقال وصالة يوجا مع إطلالات على النيل 24/7.',
                'building' => 'مبنى العافية',
                'floor' => 'الطابق الأول',
                'wing' => 'جناح اللياقة',
            ],
            'pharaoh-meeting-room' => [
                'name' => 'قاعة اجتماعات الفرعون',
                'description' => 'قاعة عملية ومرنة تناسب ورش العمل التدريبية والاجتماعات المتوسطة مع إضاءة طبيعية وافرة وتجهيزات متكاملة في قلب القاهرة.',
                'short_description' => 'قاعة متعددة الاستخدامات مثالية لجلسات التدريب والعمل الجماعي والاجتماعات.',
                'building' => 'مركز المؤتمرات',
                'floor' => 'الطابق الأول',
                'wing' => 'الجناح الجنوبي',
            ],
        ];

        foreach ($facilities as $slug => $data) {
            Facility::where('slug', $slug)->first()?->setTranslations('ar', $data);
        }
    }

    protected function seedRestaurants(): void
    {
        $restaurants = [
            'nile-grill' => [
                'name' => 'مطعم النيل جريل',
                'description' => 'مطعم الشواء والمأكولات البحرية الأسطوري في فندق سمارتيل القاهرة، يقدم أجود القطع اللحمية والمأكولات البحرية الطازجة مع إطلالات بانورامية على نهر النيل.',
                'cuisine' => 'مشويات ومأكولات بحرية',
                'dress_code' => 'أنيق غير رسمي (Smart Casual)',
                'location' => 'الطابق الأول - جناح النيل',
            ],
            'cairo-cafe-terrace' => [
                'name' => 'كافيه القاهرة والتراس',
                'description' => 'كافيه وجلسات مفتوحة على السطح مع إطلالات بانورامية على النيل وأفق القاهرة، تقدم القهوة العربية المختصة والشيشة المصرية الأصيلة والمزة الخفيفة.',
                'cuisine' => 'كافيه ومزة متوسطية',
                'dress_code' => 'غير رسمي (Casual)',
                'location' => 'تراس السطح',
            ],
            'saraya-egyptian-kitchen' => [
                'name' => 'مطعم سرايا المطبخ المصري',
                'description' => 'عرض بوفيه وأطباق à la carte يوميًا يستعرض أشهى المأكولات المصرية الأصيلة من الكشري والفول المدمس إلى الكفتة المشوية والمكرونة بالبشاميل.',
                'cuisine' => 'مأكولات مصرية وعالمية',
                'dress_code' => 'غير رسمي (Casual)',
                'location' => 'مستوى اللوبي - الطابق الأرضي',
            ],
        ];

        foreach ($restaurants as $slug => $data) {
            Restaurant::where('slug', $slug)->first()?->setTranslations('ar', $data);
        }
    }

    protected function seedMenus(): void
    {
        $categories = [
            'Appetizers' => 'المقبلات والشوربات',
            'Starters' => 'المقبلات الخفيفة',
            'Mains' => 'الأطباق الرئيسية',
            'Main Courses' => 'الأطباق الرئيسية',
            'Seafood' => 'المأكولات البحرية',
            'Desserts' => 'الحلويات الفاخرة',
            'Beverages' => 'المشروبات والكوكتيلات',
            'Pastries' => 'المخبوزات والحلويات',
            'Salads' => 'السلطات الطازجة',
        ];

        foreach ($categories as $en => $ar) {
            MenuCategory::where('name', $en)->get()->each(fn ($cat) => $cat->setTranslations('ar', ['name' => $ar]));
        }

        $items = [
            'Grilled Atlantic Salmon' => [
                'name' => 'سلمون أطلسي مشوي',
                'description' => 'فيليه سلمون مشوي على اللهب يقدم مع نبات الهليون وصلصة الليمون والأعشاب البرية.',
            ],
            'Ribeye Steak' => [
                'name' => 'ستيك ريب آي أنجوس',
                'description' => 'لحم بقر بلاك أنجوس مشوي بعناية مع بطاطس مهروسة بالكمأة وصلصة الفلفل الأسود.',
            ],
            'Mediterranean Mezze Platter' => [
                'name' => 'تشكيلة المزة المتوسطية',
                'description' => 'حمص، متبل باذنجان، ورق عنب، كبة مقلية مع خبز البيتا الطازج.',
            ],
            'Classic Caesar Salad' => [
                'name' => 'سلطة سيزر الكلاسيكية',
                'description' => 'خس روماني مقرمش، قطع خبز محمصة، جبن بارميزان مع صلصة السيزر التقليدية وصدر دجاج مشوي.',
            ],
            'Tiramisu' => [
                'name' => 'تيراميسو إيطالي كلاسيكي',
                'description' => 'بسكويت سافوياردي مغموس بقهوة الإسبريسو مع طبقات كريمة الماسكاربوني وبودرة الكاكاو.',
            ],
            'Artisan Croissant' => [
                'name' => 'كرواسون زبدة فرنسي',
                'description' => 'مخبوز طازج يومياً بطبقات زبدة فرنسية هشة وقرمشة مثالية.',
            ],
            'Signature Cappuccino' => [
                'name' => 'كابتشينو هورايزون الخاص',
                'description' => 'قهوة إسبريسو مزدوجة غنية مع رغوة حليب ناعمة ورشة قرفة.',
            ],
        ];

        foreach ($items as $en => $data) {
            MenuItem::where('name', $en)->get()->each(fn ($item) => $item->setTranslations('ar', $data));
        }
    }

    protected function seedRooms(): void
    {
        $rooms = [
            'accessible-king-room' => [
                'name' => 'غرفة كينغ مجهزة لأصحاب الهمم',
                'description' => 'غرفة رحبة مصممة بعناية لتوفير أقصى درجات الراحة وسهولة الحركة مع حمام مجهز بالكامل وممرات واسعة.',
                'short_description' => 'غرفة كينغ فسيحة مجهزة بكافة متطلبات الراحة وسهولة الوصول.',
                'bed_type' => 'سرير كينغ مريح',
                'view' => 'إطلالة على الحديقة',
            ],
            'double-queen-room' => [
                'name' => 'غرفة ديلوكس بسريرين كوين',
                'description' => 'مثالية للعائلات أو الأصدقاء، تتميز بسريرين كوين مريحين ومساحة جلوس متميزة وإطلالة رائعة على المسبح.',
                'short_description' => 'غرفة فندقية واسعة تتسع للعائلات بسريرين كوين فاخرين.',
                'bed_type' => 'سريران كوين فاخران',
                'view' => 'إطلالة على المسبح',
            ],
            'executive-king-suite' => [
                'name' => 'جناح كينغ تنفيذي فاخر',
                'description' => 'جناح راقٍ يشتمل على غرفة نوم رئيسية وصالة معيشة منفصلة وإمكانية الدخول المجاني للاونج التنفيذي وخدمة الغرف المتميزة.',
                'short_description' => 'جناح تنفيذي مع صالة مستقلة ودخول حصري للاونج التنفيذي.',
                'bed_type' => 'سرير كينغ ماستر',
                'view' => 'إطلالة بانورامية على البحر',
            ],
            'grand-horizon-suite' => [
                'name' => 'جناح جراند هورايزون الرئاسي',
                'description' => 'قمة الفخامة والرفاهية: جناح ملكي فسيح يضم غرفتي نوم وصالة طعام وغرفة معيشة فارهة وتراس خاص بإطلالة بحرية خلابة.',
                'short_description' => 'أفخم أجنحة الفندق بإطلالة ساحرة بزاوية 180 درجة ومرافق ملكية متكاملة.',
                'bed_type' => 'سرير كينغ ملكي فائق الفخامة',
                'view' => 'إطلالة بانورامية كاملة على المحيط',
            ],
            'junior-suite' => [
                'name' => 'جناح جونيور الأنيق',
                'description' => 'مزيج رائع بين الراحة والمساحة، يضم منطقة جلوس أنيقة مدمجة ومكتب عمل وحمام رخامي فخم.',
                'short_description' => 'جناح متوسط فسيح يجمع بين غرفة النوم ومنطقة معيشة مريحة.',
                'bed_type' => 'سرير كينغ كبير',
                'view' => 'إطلالة جزئية على البحر',
            ],
            'king-guest-room' => [
                'name' => 'غرفة كينغ كلاسيكية',
                'description' => 'غرفة نوم عصرية مؤثثة بأرقى المفروشات وتضم سرير كينغ مريح وحمام رخامي مع دش استحمام مطري وإطلالة مبهجة.',
                'short_description' => 'غرفة ضيوف مريحة وأنيقة مجهزة بكل ما تحتاجه لإقامة هانئة.',
                'bed_type' => 'سرير كينغ فاخر',
                'view' => 'إطلالة على المدينة',
            ],
        ];

        foreach ($rooms as $slug => $data) {
            Room::where('slug', $slug)->first()?->setTranslations('ar', $data);
        }
    }

    protected function seedServices(): void
    {
        $services = [
            'airport-transfer' => [
                'name' => 'خدمة النقل من وإلى المطار',
                'description' => 'سيارات ليموزين فاخرة وسائقون محترفون لضمان وصولك ومغادرتك بكل راحة ويسر وأمان.',
                'availability' => 'على مدار 24 ساعة (بحجز مسبق)',
            ],
            'concierge' => [
                'name' => 'خدمات الكونسيرج الممتازة',
                'description' => 'مساعد شخصي لحجوزات المطاعم، الفعاليات الثقافية، الجولات السياحية وتلبية كافة طلباتكم الخاصة.',
                'availability' => 'يومياً من 07:00 ص إلى 11:00 م',
            ],
            'housekeeping' => [
                'name' => 'خدمة تنظيف الغرف اليومية',
                'description' => 'عناية فائقة بنظافة وترتيب غرفتك وتبديل الشراشف والمناشف وخدمة ترتيب الأسرّة المسائية.',
                'availability' => 'يومياً مع خدمة عند الطلب',
            ],
            'laundry' => [
                'name' => 'خدمة الغسيل والتنظيف الجاف',
                'description' => 'تنظيف جاف سريع وغسيل وكي ملابس عالي الجودة مع خدمة التسليم في نفس اليوم.',
                'availability' => 'من 08:00 ص إلى 08:00 م',
            ],
            'room-service' => [
                'name' => 'خدمة تناول الطعام في الغرفة',
                'description' => 'قائمة طعام شاملة تضم أشهى الأطباق والمشروبات تقدم ساخنة وطازجة إلى باب غرفتك.',
                'availability' => 'متاحة 24 ساعة يومياً',
            ],
            'tech-support' => [
                'name' => 'الدعم الفني والتقني',
                'description' => 'مساعدة فورية لحل مشاكل الاتصال بالإنترنت، الأجهزة الذكية وأنظمة الصوت والصورة في الغرفة.',
                'availability' => 'على مدار 24 ساعة',
            ],
            'valet-parking' => [
                'name' => 'خدمة صف السيارات (فاليه)',
                'description' => 'خدمة صف واسترجاع السيارات بسرعة وأمان مع مواقف مغطاة ومحمية بالكامل.',
                'availability' => 'متاحة 24/7 عند المدخل الرئيسي',
            ],
            'wake-up-call' => [
                'name' => 'خدمة الإيقاظ الهاتفي',
                'description' => 'اتصال لطيف وموثوق في الموعد المحدد الذي تختاره لبدء يومك بنشاط ومواعيد منضبطة.',
                'availability' => 'حسب الطلب عبر زر الهاتف أو الاستقبال',
            ],
        ];

        foreach ($services as $slug => $data) {
            Service::where('slug', $slug)->first()?->setTranslations('ar', $data);
        }
    }

    protected function seedEvents(): void
    {
        $events = [
            'hilton-honors-member-mixer' => [
                'title' => 'ملتقى أعضاء هيلتون أونورز الحصري',
                'description' => 'أمسية تعارف وضيافة حصرية لأعضاء برنامج هيلتون أونورز مع مقبلات مختارة ومشروبات ترحيبية راقية.',
                'short_description' => 'أمسية خاصة لأعضاء هيلتون أونورز للاستمتاع بضيافة استثنائية.',
                'location' => 'اللاونج التنفيذي - الطابق 18',
            ],
            'live-music-night' => [
                'title' => 'ليلة الموسيقى الحية والجاز',
                'description' => 'استمتع بأعذب ألحان الجاز والساكسفون يؤديها فنانون عالميون في أجواء دافئة تسلب القلوب.',
                'short_description' => 'أمسية موسيقية رائعة تضفي أجواء رومانسية لا تُنسى.',
                'location' => 'سكاي لاونج - السطح',
            ],
            'mediterranean-night' => [
                'title' => 'ليالي النكهات المتوسطية',
                'description' => 'بوفيه عشاء مفتوح يستعرض أشهى أطباق المطبخ المتوسطي من اليونان وإيطاليا وإسبانيا مع محطات طهي حية.',
                'short_description' => 'رحلة تذوق غنية بالنكهات المتوسطية الأصيلة مع طهاة متخصصين.',
                'location' => 'مطعم أزور البحري',
            ],
            'rooftop-pool-party' => [
                'title' => 'حفلة المسبح الصيفية على السطح',
                'description' => 'أجواء صيفية منعشة بجوار المسبح مع أنغام الدي جي ومشروبات منعشة وإطلالة مذهلة على الغروب.',
                'short_description' => 'أنغام موسيقية ومشروبات صيفية منعشة بجوار المسبح اللامتناهي.',
                'location' => 'مسبح السطح هورايزون',
            ],
            'sunset-yoga' => [
                'title' => 'جلسة يوغا وتأمل عند الغروب',
                'description' => 'جلسة استرخاء وتناغم جسدي وذهني بصحبة مدربة يوغا محترفة في الهواء الطلق مع هبوط الشمس خلف الأفق.',
                'short_description' => 'استعد سلامك الداخلي مع جلسة يوغا هادئة على شاطئ البحر.',
                'location' => 'تراس نادي شاطئ هورايزون',
            ],
        ];

        foreach ($events as $slug => $data) {
            Event::where('slug', $slug)->first()?->setTranslations('ar', $data);
        }
    }

    protected function seedOffers(): void
    {
        $offers = [
            'dinner-for-two' => [
                'title' => 'باقة العشاء الرومانسي لشخصين',
                'description' => 'استمتع بعشاء فاخر مكون من 4 أطباق مصممة خصيصاً لكما على ضوء الشموع مع إطلالة بحرية ساحرة في مطعم أزور.',
                'short_description' => 'تجربة طعام رومانسية خاصة متكاملة لشخصين تشمل 4 أطباق فاخرة.',
                'badge' => 'الأكثر رومانسية',
                'discount' => 'خصم 20%',
            ],
            'family-package' => [
                'title' => 'باقة العطلة العائلية المتكاملة',
                'description' => 'إقامة مريحة تشمل إفطاراً مجانياً لجميع أفراد العائلة ودخولاً مجانياً للأطفال لنادي المغامرات وخصومات حصرية على الوجبات.',
                'short_description' => 'إقامة مبهجة لجميع أفراد الأسرة مع مزايا خاصة للأطفال وإفطار مجاني.',
                'badge' => 'المفضلة للعائلات',
                'discount' => 'إفطار مجاني + خصم 15%',
            ],
            'honors-member-rate' => [
                'title' => 'سعر خاص حصري لأعضاء هيلتون أونورز',
                'description' => 'احصل على أفضل سعر مضمون مع نقاط مكافآت مضاعفة وإمكانية تسجيل مغادرة متأخر مجاناً عند الحجز المباشر.',
                'short_description' => 'مزايا حصرية ونقاط مكافآت إضافية لأعضاء برنامج الولاء.',
                'badge' => 'خاص بالأعضاء',
                'discount' => 'توفير حتى 25%',
            ],
            'spa-package' => [
                'title' => 'باقة التجدد والسكينة في إيفوريا سبا',
                'description' => 'يوم كامل من الدلال والاسترخاء يشمل جلسة مساج لكامل الجسم لمدة 60 دقيقة وعلاجاً للوجه واستخداماً خاصاً لمرافق الساونا والبخار.',
                'short_description' => 'جلسات تدليك وعناية بالبشرة متكاملة لاستعادة النشاط والحيوية.',
                'badge' => 'استرخاء مطلق',
                'discount' => 'باقة مخفضة',
            ],
            'weekend-escape' => [
                'title' => 'ملاذ عطلة نهاية الأسبوع الفاخر',
                'description' => 'اهرب من صخب الحياة اليومية واستمتع بإقامة استجمامية لعطلة نهاية الأسبوع تشمل رصيداً لتناول الطعام ومغادرة متأخرة حتى 4 عصراً.',
                'short_description' => 'عطلة أسبوعية منعشة تشمل رصيد طعام وتسجيل مغادرة متأخر.',
                'badge' => 'عرض خاص',
                'discount' => 'رصيد 50$ مجاناً',
            ],
        ];

        foreach ($offers as $slug => $data) {
            Offer::where('slug', $slug)->first()?->setTranslations('ar', $data);
        }
    }

    protected function seedExperiences(): void
    {
        $experiences = [
            'bay-sunset-cruise' => [
                'title' => 'رحلة بحرية خاصة باليخت عند الغروب',
                'description' => 'أبحر على متن يخت فاخر في مياه الخليج الهادئة مع مشاهدة غروب الشمس البديع وتناول المقبلات والمشروبات المنعشة.',
                'short_description' => 'جولة بحرية ساحرة لمدة ساعتين على متن يخت خاص.',
                'duration' => 'ساعتان',
            ],
            'cooking-class' => [
                'title' => 'ورشة الطهي المتوسطي مع رئيس الطهاة',
                'description' => 'تعلم أسرار إعداد أشهر الأطباق الإيطالية والبحرية في تجربة تفاعلية ممتعة يقودها كبير طهاة الفندق في مطبخ احترافي.',
                'short_description' => 'درس طهي تفاعلي ممتع يختتم بتناول الأطباق المحضرة.',
                'duration' => '3 ساعات',
            ],
            'kids-adventure-day' => [
                'title' => 'يوم استكشاف الطبيعة للأطفال',
                'description' => 'مغامرة ترفيهية متكاملة تشمل البحث عن الكنز وورش الأشغال اليدوية وألعاب شاطئية آمنة تمنح أطفالكم ذكريات لا تُنسى.',
                'short_description' => 'أنشطة وألعاب خارجية ممتعة ومحفزة للأطفال طوال اليوم.',
                'duration' => '4 ساعات',
            ],
            'sunrise-yoga' => [
                'title' => 'يوغا وتأمل الشروق على الشاطئ',
                'description' => 'استقبل خيوط الصباح الأولى بنشاط وحيوية مع جلسة يوغا شاطئية موجهة تعزز مرونة الجسم وسلام الروح.',
                'short_description' => 'جلسة يوغا صباحية هادئة على الرمال الناعمة مع بزوغ الفجر.',
                'duration' => '60 دقيقة',
            ],
            'sunset-dinner' => [
                'title' => 'عشاء كابانا خاص على ضفاف البحر',
                'description' => 'طاولة خاصة معدة على الرمال وسط شموع متوهجة وقائمة طعام فاخرة مخصصة يقدمها نادل شخصي لخدمتكما.',
                'short_description' => 'أمسية عشاء استثنائية منعزلة على شاطئ البحر تحت أضواء النجوم.',
                'duration' => 'ساعتان ونصف',
            ],
        ];

        foreach ($experiences as $slug => $data) {
            Experience::where('slug', $slug)->first()?->setTranslations('ar', $data);
        }
    }

    protected function seedInfoEntries(): void
    {
        $translations = [
            // Timing entries
            'Check-in' => ['label' => 'تسجيل الوصول', 'group' => 'الفندق والإقامة', 'value' => '15:00'],
            'Check-out' => ['label' => 'تسجيل المغادرة', 'group' => 'الفندق والإقامة', 'value' => '11:00'],
            'Breakfast' => ['label' => 'بوفيه الإفطار', 'group' => 'المطاعم والوجبات', 'value' => '06:30 - 10:30'],
            'Azure Restaurant' => ['label' => 'مطعم أزور البحري', 'group' => 'المطاعم والوجبات', 'value' => '12:00 - 23:00'],
            'Sky Lounge' => ['label' => 'سكاي لاونج', 'group' => 'المطاعم والوجبات', 'value' => '17:00 - 01:00'],
            'Rooftop Pool' => ['label' => 'مسبح السطح', 'group' => 'الأنشطة والترفيه', 'value' => '07:00 - 20:00'],
            'eforea Spa' => ['label' => 'سبا إيفوريا', 'group' => 'الأنشطة والترفيه', 'value' => '09:00 - 21:00'],
            'Fitness Center' => ['label' => 'مركز اللياقة البدنية', 'group' => 'الأنشطة والترفيه', 'value' => 'مفتوح 24 ساعة'],
            'Executive Lounge' => ['label' => 'اللاونج التنفيذي', 'group' => 'الفندق والإقامة', 'value' => '06:30 - 22:00'],

            // Short calls
            'Front Desk' => ['label' => 'مكتب الاستقبال', 'value' => '0'],
            'Concierge' => ['label' => 'الكونسيرج والإرشاد', 'value' => '11'],
            'Room Service' => ['label' => 'خدمة الغرف', 'value' => '12'],
            'Housekeeping' => ['label' => 'خدمة تنظيف الغرف', 'value' => '14'],
            'eforea Spa' => ['label' => 'سبا إيفوريا', 'value' => '16'],
            'IT & Tech Support' => ['label' => 'الدعم الفني والإنترنت', 'value' => '15'],
            'Valet & Bell Desk' => ['label' => 'خدمة صف السيارات والأمتعة', 'value' => '18'],
            'Emergency' => ['label' => 'الطوارئ والأمن', 'value' => '99'],
        ];

        foreach (InfoEntry::all() as $entry) {
            if (isset($translations[$entry->label])) {
                $entry->setTranslations('ar', $translations[$entry->label]);
            }
        }
    }

    protected function seedHotel(): void
    {
        $hotel = Hotel::where('slug', 'hilton-grand-horizon')->first() ?? Hotel::first();
        if ($hotel) {
            $hotel->setTranslations('ar', [
                'name' => 'فندق سمارتيل',
                'address' => '١١١٣ كورنيش النيل، جاردن سيتي، القاهرة، مصر',
            ]);
        }
    }

    protected function seedPages(): void
    {
        $hotel = Hotel::where('slug', 'hilton-grand-horizon')->first() ?? Hotel::first();
        if (! $hotel) {
            return;
        }

        // 1. Homepage
        $home = Page::withoutGlobalScopes()->where('hotel_id', $hotel->id)->where('is_home', true)->first();
        if ($home) {
            $home->setTranslations('ar', [
                'name' => 'الصفحة الرئيسية',
                'seo_title' => 'فندق هيلتون جراند هورايزون | إقامة استثنائية على الواجهة البحرية',
                'seo_description' => 'استمتع بإقامة فاخرة وإطلالات بحرية ساحرة ومرافق عالمية المستوى في هيلتون جراند هورايزون.',
            ]);

            foreach ([$home->draftVersion, $home->publishedVersion] as $version) {
                if (! $version) {
                    continue;
                }
                $sections = $version->sections['sections'] ?? $version->sections ?? [];
                if (! is_array($sections)) {
                    continue;
                }

                $arabicProps = [
                    'hero' => [
                        'title' => 'مرحباً بكم في هيلتون جراند هورايزون',
                        'subtitle' => 'اكتشف تجربة فندقية لا تُنسى وأجواء ساحرة على ضفاف الخليج.',
                        'button_text' => 'استكشف مرافقنا',
                    ],
                    'story-slideshow' => [
                        'title' => 'مرحباً بكم في هيلتون جراند هورايزون',
                        'subtitle' => 'اكتشف إقامة استثنائية وأجواء فاخرة على الواجهة البحرية.',
                    ],
                    'facility-grid' => [
                        'title' => 'استكشف مرافقنا الفاخرة',
                        'description' => 'كل ما تحتاجه لإقامة مثالية ومريحة تجمع بين الرفاهية والهدوء.',
                    ],
                    'restaurant-grid' => [
                        'title' => 'المطاعم وتجارب الطهي في جراند هورايزون',
                        'description' => 'تذوق أشهى المأكولات العالمية المحضرة بأيدي نخبة من أمهر الطهاة.',
                    ],
                    'offers' => [
                        'title' => 'العروض والباقات الخاصة',
                    ],
                    'events' => [
                        'title' => 'الفعاليات والأنشطة القادمة',
                    ],
                    'cta' => [
                        'heading' => 'هل أنت مستعد لحجز إقامتك الاستثنائية؟',
                        'subheading' => 'فريق الكونسيرج وخدمة الضيوف مستعدون لمساعدتكم في التخطيط لإقامة لا تُنسى.',
                        'button_text' => 'تواصل معنا',
                    ],
                ];

                $updated = false;
                foreach ($sections as &$sec) {
                    $type = $sec['type'] ?? '';
                    if (isset($arabicProps[$type])) {
                        $sec['translations'] = $sec['translations'] ?? [];
                        $sec['translations']['ar'] = [
                            'props' => $arabicProps[$type],
                        ];
                        foreach ($arabicProps[$type] as $k => $v) {
                            $sec['props']["{$k}_ar"] = $v;
                        }
                        $updated = true;
                    }
                }
                unset($sec);

                if ($updated) {
                    if (isset($version->sections['sections'])) {
                        $version->sections = ['sections' => $sections];
                    } else {
                        $version->sections = $sections;
                    }
                    $version->save();
                }
            }
        }

        // 2. About Page
        $about = Page::withoutGlobalScopes()->where('hotel_id', $hotel->id)->where('slug', 'about')->first();
        if ($about) {
            $about->setTranslations('ar', [
                'name' => 'عن الفندق',
                'seo_title' => 'عن فندق هيلتون جراند هورايزون | تاريخ من الفخامة والضيافة',
                'seo_description' => 'تعرف على تاريخ هيلتون جراند هورايزون ورؤيتنا في تقديم أرقى تجارب الضيافة البحرية.',
            ]);

            foreach ([$about->draftVersion, $about->publishedVersion] as $version) {
                if (! $version) {
                    continue;
                }
                $sections = $version->sections['sections'] ?? $version->sections ?? [];
                if (! is_array($sections)) {
                    continue;
                }

                $updated = false;
                foreach ($sections as &$sec) {
                    if (($sec['type'] ?? '') === 'text') {
                        $sec['translations'] = ['ar' => ['props' => [
                            'heading' => 'عن فندق هيلتون جراند هورايزون',
                            'body' => "يرحب فندق هيلتون جراند هورايزون بضيوفه على ضفاف الخليج منذ أكثر من عقدين، جامعاً بين كرم الضيافة وأرقى معايير الراحة العصرية.\n\nمن مطاعمنا الحائزة على جوائز إلى السبا الهادئ، تم تصميم كل تفصيل ليمنحكم إقامة استثنائية تفوق التوقعات.",
                        ]]];
                        $sec['props']['heading_ar'] = 'عن فندق هيلتون جراند هورايزون';
                        $sec['props']['body_ar'] = "يرحب فندق هيلتون جراند هورايزون بضيوفه على ضفاف الخليج منذ أكثر من عقدين، جامعاً بين كرم الضيافة وأرقى معايير الراحة العصرية.\n\nمن مطاعمنا الحائزة على جوائز إلى السبا الهادئ، تم تصميم كل تفصيل ليمنحكم إقامة استثنائية تفوق التوقعات.";
                        $updated = true;
                    } elseif (($sec['type'] ?? '') === 'experiences') {
                        $sec['translations'] = ['ar' => ['props' => [
                            'title' => 'التجارب الاستثنائية',
                            'description' => 'لحظات منتقاة بعناية صُممت خصيصاً لتناسب تطلعاتكم وأوقاتكم الثمينة.',
                        ]]];
                        $sec['props']['title_ar'] = 'التجارب الاستثنائية';
                        $sec['props']['description_ar'] = 'لحظات منتقاة بعناية صُممت خصيصاً لتناسب تطلعاتكم وأوقاتكم الثمينة.';
                        $updated = true;
                    }
                }
                unset($sec);

                if ($updated) {
                    if (isset($version->sections['sections'])) {
                        $version->sections = ['sections' => $sections];
                    } else {
                        $version->sections = $sections;
                    }
                    $version->save();
                }
            }
        }
    }
}
