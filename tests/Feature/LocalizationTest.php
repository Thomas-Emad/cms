<?php

namespace Tests\Feature;

use App\Models\EntityTranslation;
use App\Models\Facility;
use App\Models\Hotel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class LocalizationTest extends TestCase
{
    use RefreshDatabase;

    protected Hotel $hotel;

    protected function setUp(): void
    {
        parent::setUp();

        $this->hotel = Hotel::create([
            'name' => 'Grand Horizon Hotel',
            'slug' => 'grand-horizon',
            'status' => 'active',
        ]);

        $this->hotel->settings()->create([
            'default_locale' => 'en',
        ]);
    }

    public function test_default_locale_is_english_and_direction_is_ltr(): void
    {
        $response = $this->get('/facilities');

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page
            ->where('locale', 'en')
            ->where('direction', 'ltr')
            ->has('translations')
        );
    }

    public function test_locale_update_stores_locale_in_session_and_cookie(): void
    {
        $response = $this->post(route('locale.update'), [
            'locale' => 'ar',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('locale', 'ar');
        $response->assertPlainCookie('locale', 'ar');
    }

    public function test_invalid_locale_is_rejected(): void
    {
        $response = $this->post(route('locale.update'), [
            'locale' => 'fr',
        ]);

        $response->assertSessionHasErrors('locale');
    }

    public function test_arabic_session_sets_locale_to_ar_and_direction_to_rtl(): void
    {
        $response = $this->withSession(['locale' => 'ar'])->get('/facilities');

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page
            ->where('locale', 'ar')
            ->where('direction', 'rtl')
        );
    }

    public function test_arabic_cookie_sets_locale_to_ar_and_direction_to_rtl(): void
    {
        $response = $this->withUnencryptedCookie('locale', 'ar')->get('/facilities');

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page
            ->where('locale', 'ar')
            ->where('direction', 'rtl')
        );
    }

    public function test_hotel_default_locale_is_used_when_no_session_or_cookie(): void
    {
        $this->hotel->settings()->update(['default_locale' => 'ar']);

        $response = $this->get('/facilities');

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page
            ->where('locale', 'ar')
            ->where('direction', 'rtl')
        );
    }

    public function test_entity_translation_returns_arabic_when_locale_is_ar(): void
    {
        $facility = Facility::create([
            'hotel_id' => $this->hotel->id,
            'name' => 'Infinity Pool',
            'slug' => 'infinity-pool',
            'category' => 'pool',
            'status' => 'published',
            'description' => 'A luxury rooftop swimming pool.',
        ]);

        EntityTranslation::create([
            'translatable_type' => Facility::class,
            'translatable_id' => $facility->id,
            'locale' => 'ar',
            'data' => [
                'name' => 'المسبح اللامتناهي',
                'description' => 'مسبح فاخر على السطح بإطلالة بانورامية ساحرة.',
            ],
        ]);

        // Default English
        app()->setLocale('en');
        $this->assertSame('Infinity Pool', $facility->name);
        $this->assertSame('A luxury rooftop swimming pool.', $facility->description);

        // Switched to Arabic
        app()->setLocale('ar');
        $this->assertSame('المسبح اللامتناهي', $facility->name);
        $this->assertSame('مسبح فاخر على السطح بإطلالة بانورامية ساحرة.', $facility->description);
    }

    public function test_entity_translation_falls_back_to_english_for_untranslated_attribute(): void
    {
        $facility = Facility::create([
            'hotel_id' => $this->hotel->id,
            'name' => 'Fitness Center',
            'slug' => 'fitness-center',
            'category' => 'fitness',
            'status' => 'published',
            'description' => 'Modern fitness equipment.',
        ]);

        EntityTranslation::create([
            'translatable_type' => Facility::class,
            'translatable_id' => $facility->id,
            'locale' => 'ar',
            'data' => [
                'name' => 'مركز اللياقة البدنية',
                // description is omitted
            ],
        ]);

        app()->setLocale('ar');
        $this->assertSame('مركز اللياقة البدنية', $facility->name);
        // Untranslated attribute transparently falls back to base English attribute
        $this->assertSame('Modern fitness equipment.', $facility->description);
    }

    public function test_backend_translation_helper_resolves_php_locale_files_in_both_locales(): void
    {
        app()->setLocale('en');
        $this->assertSame('Restaurant created. Add a menu below.', __('admin.messages.restaurant_created'));
        $this->assertSame('Hotel Timings', __('info.timing_title'));
        $this->assertSame('Pages', __('nav.pages'));
        $this->assertSame('Save', __('common.save'));

        app()->setLocale('ar');
        $this->assertSame('تم إنشاء المطعم بنجاح. أضف قائمة الطعام بالأسفل.', __('admin.messages.restaurant_created'));
        $this->assertSame('أوقات العمل والمواعيد', __('info.timing_title'));
        $this->assertSame('الصفحات', __('nav.pages'));
        $this->assertSame('حفظ', __('common.save'));
    }

    public function test_translations_prop_in_inertia_includes_php_locale_groups(): void
    {
        $response = $this->withSession(['locale' => 'ar'])->get('/facilities');

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page
            ->where('locale', 'ar')
            ->where('direction', 'rtl')
            ->where('translations.admin.messages.restaurant_created', 'تم إنشاء المطعم بنجاح. أضف قائمة الطعام بالأسفل.')
            ->where('translations.info.timing_title', 'أوقات العمل والمواعيد')
            ->where('translations.nav.pages', 'الصفحات')
            ->where('translations.common.save', 'حفظ')
        );
    }

    public function test_all_php_locale_files_exist_and_have_matching_keys(): void
    {
        $enFiles = glob(base_path('lang/en/*.php')) ?: [];
        $this->assertNotEmpty($enFiles);

        foreach ($enFiles as $file) {
            $name = basename($file);
            $arFile = base_path("lang/ar/{$name}");
            $this->assertFileExists($arFile, "Missing Arabic counterpart for lang/en/{$name}");

            $en = require $file;
            $ar = require $arFile;

            $this->assertIsArray($en, "lang/en/{$name} must return an array");
            $this->assertIsArray($ar, "lang/ar/{$name} must return an array");

            // Check top-level keys
            $this->assertSame(
                array_keys($en),
                array_keys($ar),
                "Key mismatch between lang/en/{$name} and lang/ar/{$name}"
            );
        }
    }
}
