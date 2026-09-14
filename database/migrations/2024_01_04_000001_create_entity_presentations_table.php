<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Deliberately a SEPARATE table from `pages`, not a nullable
 * presentable_type/presentable_id pair bolted onto `pages`. Reasons:
 *   - Standalone Pages have a `slug` guests visit directly; an entity
 *     presentation's URL is always the entity's own guest route
 *     (/restaurants/{slug}) - conflating the two would mean half of
 *     `pages`' columns are meaningless for one of the two row "kinds".
 *   - Keeps every existing Page/PageVersion query, test, and migration
 *     completely untouched - this is pure addition, zero risk to what
 *     already works.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('entity_presentations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hotel_id')->constrained()->cascadeOnDelete();
            $table->string('presentable_type');
            $table->unsignedBigInteger('presentable_id');
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
            $table->timestamps();

            $table->unique(['presentable_type', 'presentable_id']); // one presentation per entity
            $table->index(['hotel_id', 'presentable_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('entity_presentations');
    }
};
