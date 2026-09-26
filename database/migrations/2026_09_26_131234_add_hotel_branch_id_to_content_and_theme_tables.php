<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tables that can belong to a specific branch or be hotel-wide.
     *
     * @var string[]
     */
    protected array $tables = [
        'themes',
        'facilities',
        'restaurants',
        'rooms',
        'services',
        'events',
        'offers',
        'experiences',
        'pages',
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        foreach ($this->tables as $tableName) {
            if (Schema::hasTable($tableName) && ! Schema::hasColumn($tableName, 'hotel_branch_id')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->foreignId('hotel_branch_id')
                        ->nullable()
                        ->after('hotel_id')
                        ->constrained('hotel_branches')
                        ->nullOnDelete();
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        foreach ($this->tables as $tableName) {
            if (Schema::hasTable($tableName) && Schema::hasColumn($tableName, 'hotel_branch_id')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->dropConstrainedForeignId('hotel_branch_id');
                });
            }
        }
    }
};
