<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('entity_translations', function (Blueprint $table) {
            $table->id();
            $table->morphs('translatable');
            $table->string('locale', 10);
            $table->json('data');
            $table->timestamps();

            $table->unique(['translatable_type', 'translatable_id', 'locale'], 'entity_trans_lookup_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('entity_translations');
    }
};
