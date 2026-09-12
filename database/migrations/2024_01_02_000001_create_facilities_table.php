<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('facilities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hotel_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->string('short_description')->nullable();
            $table->enum('category', [
                'wellness', 'fitness', 'pool', 'kids', 'business', 'beach', 'meeting', 'other',
            ])->default('other');

            // Location metadata - see Section 15 (Phase 6 will build on these,
            // not add new columns).
            $table->string('building')->nullable();
            $table->string('floor')->nullable();
            $table->string('wing')->nullable();
            $table->float('pos_x')->nullable();
            $table->float('pos_y')->nullable();

            $table->json('opening_hours')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->json('amenities')->nullable();
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
            $table->boolean('featured')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['hotel_id', 'slug']);
            $table->fullText(['name', 'description', 'short_description']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('facilities');
    }
};
