<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('hotel_branches', function (Blueprint $table) {
            $table->string('guest_layout')->nullable()->after('features'); // 'classic', 'tv', or null (inherits hotel master layout)
            $table->json('metadata')->nullable()->after('guest_layout'); // branch-specific layout knobs and menu items
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hotel_branches', function (Blueprint $table) {
            $table->dropColumn(['guest_layout', 'metadata']);
        });
    }
};
