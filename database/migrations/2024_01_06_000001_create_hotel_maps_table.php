<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // One map document per hotel: floors, walkway nodes/connections and locations
        // (see resources/js/Map/types.ts for the contract). Validated by MapDataValidator.
        Schema::create('hotel_maps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hotel_id')->unique()->constrained()->cascadeOnDelete();
            $table->json('data');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hotel_maps');
    }
};
