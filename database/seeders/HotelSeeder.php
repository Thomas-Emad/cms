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
        $hotel = Hotel::updateOrCreate(
            ['slug' => 'hilton-grand-horizon'],
            [
                'name' => 'Hilton Dubai Palm Jumeirah',
                'status' => 'active',
                'contact_email' => 'info.palmjumeirah@hilton.com',
                'contact_phone' => '+971 4 230 0000',
                'address' => 'Palm West Beach, The Palm Jumeirah, Dubai, United Arab Emirates',
                'timezone' => 'Asia/Dubai',
                'currency' => 'AED',
            ]
        );

        HotelSettings::updateOrCreate(
            ['hotel_id' => $hotel->id],
            [
                'checkin_time' => '15:00:00',
                'checkout_time' => '12:00:00',
                'default_locale' => 'en',
            ]
        );

        Theme::updateOrCreate(
            ['hotel_id' => $hotel->id],
            [
                'name' => 'Hilton Dubai Palm Luxury Theme',
                'is_active' => true,
                'primary_color' => '#059669',
                'secondary_color' => '#10B981',
                'font_family' => 'Instrument Sans',
                'border_radius' => 'medium',
                'button_style' => 'rounded',
                'card_style' => 'elevated',
                'config' => [
                    'header_bg' => '#064e3b',
                    'footer_bg' => '#022c22',
                ],
            ]
        );

        User::updateOrCreate(
            ['email' => 'superadmin@gmail.com'],
            [
                'name' => 'Platform Super Admin',
                'password' => Hash::make('password'),
                'hotel_id' => null,
                'role' => 'super_admin',
                'status' => 'active',
            ]
        );

        User::updateOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'name' => 'Hilton Palm Jumeirah Admin',
                'password' => Hash::make('password'),
                'hotel_id' => $hotel->id,
                'role' => 'hotel_admin',
                'status' => 'active',
            ]
        );
    }
}
