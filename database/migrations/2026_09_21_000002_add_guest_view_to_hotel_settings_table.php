<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Site-wide guest-facing layout choice, distinct from a single Page's
     * 'layout' field (scroll/fullscreen - see pages.layout migration).
     * This is the overall guest SHELL/chrome for the whole guest site:
     *
     *   'classic' - the existing hospitality-screen shell (dark top bar +
     *                bottom dock nav, GuestLayout.vue). Default - no
     *                behavior changes for existing hotels.
     *   'tv'      - Samsung-Smart-TV-style shell (TvGuestLayout.vue): no
     *               persistent top bar/dock chrome, content (e.g. an
     *               app-launcher home page) drives its own navigation.
     */
    public function up(): void
    {
        Schema::table('hotel_settings', function (Blueprint $table) {
            $table->enum('guest_view', ['classic', 'tv'])->default('classic')->after('default_locale');
        });
    }

    public function down(): void
    {
        Schema::table('hotel_settings', function (Blueprint $table) {
            $table->dropColumn('guest_view');
        });
    }
};
