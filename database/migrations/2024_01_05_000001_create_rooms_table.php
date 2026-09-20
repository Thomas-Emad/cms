<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hotel_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->string('short_description')->nullable();
            $table->text('description')->nullable();
            $table->unsignedSmallInteger('size_sqm')->nullable();
            $table->unsignedTinyInteger('max_guests')->nullable();
            $table->string('bed_type')->nullable();
            $table->string('view')->nullable();
            // Free-form feature list ("Balcony", "Bathtub", ...), same idea as facilities.amenities.
            $table->json('features')->nullable();
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
            $table->boolean('featured')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['hotel_id', 'slug']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rooms');
    }
};
