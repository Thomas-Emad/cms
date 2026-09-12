<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hotel_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->string('icon')->nullable(); // icon key or image path
            $table->json('availability')->nullable(); // e.g. {"24_7": true} or hours
            $table->string('contact')->nullable();
            $table->decimal('price', 8, 2)->nullable(); // nullable = complimentary/on request
            $table->boolean('request_enabled')->default(false); // guest can submit a request via the app
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['hotel_id', 'slug']);
            $table->fullText(['name', 'description']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
