<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // nullable: super_admin users are not scoped to a hotel
            $table->foreignId('hotel_id')->nullable()->after('id')->constrained()->nullOnDelete();
            $table->enum('role', ['super_admin', 'hotel_admin', 'hotel_staff'])
                ->default('hotel_staff')
                ->after('hotel_id');
            $table->enum('status', ['active', 'invited', 'disabled'])->default('active')->after('role');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('hotel_id');
            $table->dropColumn(['role', 'status']);
        });
    }
};
