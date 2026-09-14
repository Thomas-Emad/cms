<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('presentation_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('entity_presentation_id')->constrained()->cascadeOnDelete();
            $table->json('sections'); // same {schema_version, sections[]} shape as page_versions.sections
            $table->enum('state', ['draft', 'published', 'archived'])->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->foreignId('published_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['entity_presentation_id', 'state']);
        });

        Schema::table('entity_presentations', function (Blueprint $table) {
            $table->foreignId('draft_version_id')->nullable()
                ->after('status')->constrained('presentation_versions')->nullOnDelete();
            $table->foreignId('published_version_id')->nullable()
                ->after('draft_version_id')->constrained('presentation_versions')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('entity_presentations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('draft_version_id');
            $table->dropConstrainedForeignId('published_version_id');
        });
        Schema::dropIfExists('presentation_versions');
    }
};
