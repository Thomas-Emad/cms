<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('restaurants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hotel_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->string('cuisine')->nullable();
            $table->string('location')->nullable(); // human-readable, distinct from structured floor/wing
            $table->string('floor')->nullable();
            $table->json('opening_hours')->nullable();
            $table->string('dress_code')->nullable();
            $table->string('phone')->nullable();
            $table->string('reservation_url')->nullable();
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
            $table->boolean('featured')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['hotel_id', 'slug']);
            $table->fullText(['name', 'description', 'cuisine']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('restaurants');
    }
};
