<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hotel_id')->constrained()->cascadeOnDelete();
            $table->string('disk')->default('public');
            $table->string('path');
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('size')->nullable();
            $table->string('alt_text')->nullable();

            // Polymorphic owner: Facility, Restaurant, Event, Offer,
            // Experience, MenuItem, etc. Nullable mediable_id would allow
            // library-only/unattached uploads later, but we don't need that
            // yet - every media row belongs to something from day one.
            $table->string('mediable_type');
            $table->unsignedBigInteger('mediable_id');

            // 'cover' (single, first-wins) or 'gallery' (many, ordered).
            // Page Builder gallery sections and guest detail pages both key
            // off this instead of separate cover_image/gallery columns.
            $table->string('collection')->default('gallery');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['mediable_type', 'mediable_id', 'collection']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media');
    }
};
