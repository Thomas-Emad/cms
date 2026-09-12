<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('themes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hotel_id')->constrained()->cascadeOnDelete();
            $table->string('name')->default('Default Theme');
            $table->boolean('is_active')->default(true);
            $table->string('primary_color', 7)->default('#1F4B5A');
            $table->string('secondary_color', 7)->default('#D4AF37');
            $table->string('font_family')->default('Inter');
            $table->enum('border_radius', ['none', 'small', 'medium', 'large', 'full'])->default('medium');
            $table->enum('button_style', ['square', 'rounded', 'pill'])->default('rounded');
            $table->enum('card_style', ['flat', 'outlined', 'elevated'])->default('elevated');
            $table->json('config')->nullable(); // overflow for future theme knobs
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('themes');
    }
};
