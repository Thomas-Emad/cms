<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // One small table for the two "label -> value" lists shown on guest
        // screens, told apart by `kind`:
        //   timing     : group "Hotel",  label "Check-in",  value "15:00"
        //   short_call : (no group),     label "IT Support", value "15"
        Schema::create('info_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hotel_id')->constrained()->cascadeOnDelete();
            $table->string('kind', 20);
            $table->string('group')->nullable();
            $table->string('label');
            $table->string('value', 100);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['hotel_id', 'kind', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('info_entries');
    }
};
