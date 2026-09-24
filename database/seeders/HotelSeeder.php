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
                'name' => 'Smarttel Hotel',
                'status' => 'active',
                'contact_email' => 'info@smarttelhotel.com',
                'contact_phone' => '+20 2 2578 0444',
                'address' => '1113 Corniche El Nil, Garden City, Cairo, Egypt',
                'timezone' => 'Africa/Cairo',
                'currency' => 'EGP',
            ]
        );

        HotelSettings::updateOrCreate(
            ['hotel_id' => $hotel->id],
            [
                'checkin_time' => '14:00:00',
                'checkout_time' => '12:00:00',
                'default_locale' => 'en',
                'metadata' => [
                    'weather' => [
                        'city' => 'Cairo',
                        'latitude' => 30.0444,
                        'longitude' => 31.2357,
                        'timezone' => 'Africa/Cairo',
                    ],
                ],
            ]
        );

        Theme::updateOrCreate(
            ['hotel_id' => $hotel->id],
            [
                'name' => 'Smarttel Luxury Theme',
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
                'name' => 'Smarttel Hotel Admin',
                'password' => Hash::make('password'),
                'hotel_id' => $hotel->id,
                'role' => 'hotel_admin',
                'status' => 'active',
            ]
        );
    }
}
