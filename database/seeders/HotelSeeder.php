<?php

namespace Database\Seeders;

use App\Models\Hotel;
use App\Models\HotelSettings;
use App\Models\Theme;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class HotelSeeder extends Seeder
{
    public function run(): void
    {
        $hotel = Hotel::create([
            'name' => 'Hilton Grand Horizon',
            'slug' => 'hilton-grand-horizon',
            'status' => 'active',
            'contact_email' => 'info@hiltongrandhorizon.example',
            'contact_phone' => '+1 555 010 2020',
            'address' => '1 Horizon Bay Drive',
            'timezone' => 'UTC',
            'currency' => 'USD',
        ]);

        HotelSettings::create([
            'hotel_id' => $hotel->id,
            'checkin_time' => '15:00:00',
            'checkout_time' => '11:00:00',
            'default_locale' => 'en',
        ]);

        // Hilton brand palette: deep Hilton Blue as primary, Hilton Gold as
        // the accent/secondary color used for CTAs, highlights and dividers.
        Theme::create([
            'hotel_id' => $hotel->id,
            'name' => 'Hilton Brand Theme',
            'is_active' => true,
            'primary_color' => '#002F61',
            'secondary_color' => '#B99A62',
            'font_family' => 'Inter',
            'border_radius' => 'medium',
            'button_style' => 'rounded',
            'card_style' => 'elevated',
        ]);

        User::create([
            'name' => 'Platform Super Admin',
            'email' => 'superadmin@gmail.com',
            'password' => Hash::make('password'),
            'hotel_id' => null,
            'role' => 'super_admin',
            'status' => 'active',
        ]);

        User::create([
            'name' => 'Grand Horizon Admin',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('password'),
            'hotel_id' => $hotel->id,
            'role' => 'hotel_admin',
            'status' => 'active',
        ]);
    }
}
