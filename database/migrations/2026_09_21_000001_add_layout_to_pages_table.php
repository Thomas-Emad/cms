<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 'scroll' is the existing behavior for every current page (normal
     * vertically-scrolling content), so it's the default - this migration
     * changes nothing about how existing pages render.
     *
     * 'fullscreen' is for main/home-style screens (e.g. the Smart-TV-style
     * home screen template): no page scroll, sections snap to fill the
     * viewport instead. See GuestLayout.vue / PageView.vue for how this
     * flag is consumed.
     */
    public function up(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->enum('layout', ['scroll', 'fullscreen'])->default('scroll')->after('is_home');
        });
    }

    public function down(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->dropColumn('layout');
        });
    }
};
