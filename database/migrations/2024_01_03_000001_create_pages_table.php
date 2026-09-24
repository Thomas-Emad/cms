<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hotel_id')->constrained()->cascadeOnDelete();
            $table->string('name'); // internal admin label, e.g. "Homepage"
            $table->string('slug'); // guest URL segment; '' is not special-cased - see is_home
            $table->boolean('is_home')->default(false);
            $table->string('seo_title')->nullable();
            $table->string('seo_description')->nullable();
            $table->foreignId('seo_og_image_media_id')->nullable()
                ->constrained('media')->nullOnDelete();
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');

            // Nullable FKs added after page_versions exists (circular dependency:
            // pages needs page_versions.id, page_versions needs pages.id) -
            // see 2024_01_03_000002 migration below, which adds these two
            // columns once page_versions is created.

            $table->timestamps();

            $table->unique(['hotel_id', 'slug']);
        });

        // MySQL has no partial unique index. This is the standard workaround:
        // a virtual generated column that's NULL unless is_home is true (in
        // which case it equals hotel_id), plus a plain unique index on it.
        // MySQL unique indexes permit unlimited NULLs but only one occurrence
        // of any given non-NULL value - so at most one is_home=true row can
        if (DB::getDriverName() === 'sqlite') {
            DB::statement('CREATE UNIQUE INDEX pages_home_marker_unique ON pages(hotel_id) WHERE is_home = 1');
        } else {
            DB::statement(
                'ALTER TABLE pages ADD COLUMN home_marker BIGINT UNSIGNED
                    GENERATED ALWAYS AS (IF(is_home = 1, hotel_id, NULL)) VIRTUAL'
            );
            DB::statement('ALTER TABLE pages ADD UNIQUE INDEX pages_home_marker_unique (home_marker)');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('pages');
    }
};
