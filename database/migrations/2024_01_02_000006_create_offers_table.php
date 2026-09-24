<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('offers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hotel_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->decimal('price', 8, 2)->nullable();
            $table->unsignedTinyInteger('discount')->nullable(); // percentage
            $table->date('valid_from')->nullable();
            $table->date('valid_until')->nullable();
            $table->string('booking_url')->nullable();
            $table->enum('status', ['draft', 'published', 'expired', 'archived'])->default('draft');
            $table->boolean('featured')->default(false);
            $table->timestamps();

            $table->unique(['hotel_id', 'slug']);
            if (Schema::getConnection()->getDriverName() !== 'sqlite') {
                $table->fullText(['title', 'description']);
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('offers');
    }
};
