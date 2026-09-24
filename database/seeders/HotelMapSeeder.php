<?php

namespace Database\Seeders;

use App\Models\Hotel;
use App\Models\HotelMap;
use Illuminate\Database\Seeder;

/** Gives the demo hotel the demo map. Idempotent: never overwrites a map that already exists. */
class HotelMapSeeder extends Seeder
{
    public function run(): void
    {
        $hotel = Hotel::where('slug', 'hilton-grand-horizon')->first();
        if (! $hotel) {
            return;
        }

        $data = json_decode((string) file_get_contents(base_path('database/data/demo-hotel-map.json')), true);
        HotelMap::withoutGlobalScopes()->updateOrCreate(
            ['hotel_id' => $hotel->id],
            ['data' => $data]
        );
    }
}
