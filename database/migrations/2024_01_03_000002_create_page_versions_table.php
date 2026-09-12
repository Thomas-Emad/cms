<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('page_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('page_id')->constrained()->cascadeOnDelete();

            // The section tree. See App\Services\PageBuilder for the schema
            // this must conform to (validated on every save, not just here).
            $table->json('sections');

            $table->enum('state', ['draft', 'published', 'archived'])->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->foreignId('published_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['page_id', 'state']);
        });

        // Now that page_versions exists, add the two pointer FKs onto pages.
        // draft_version_id is assigned once at page creation and never
        // reassigned - the draft row is mutated in place, not replaced.
        // published_version_id changes exactly once per publish, atomically,
        // via PublishPageAction (new row + pointer flip in one transaction).
        Schema::table('pages', function (Blueprint $table) {
            $table->foreignId('draft_version_id')->nullable()
                ->after('status')->constrained('page_versions')->nullOnDelete();
            $table->foreignId('published_version_id')->nullable()
                ->after('draft_version_id')->constrained('page_versions')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->dropConstrainedForeignId('draft_version_id');
            $table->dropConstrainedForeignId('published_version_id');
        });
        Schema::dropIfExists('page_versions');
    }
};
