<?php

namespace App\Services\Layout;

use App\Models\HotelSettings;

/** Reads/writes a hotel's guest layout inside hotel_settings.metadata['guest_layout'] (no extra table). */
class GuestLayoutStore
{
    public function forHotel(?int $hotelId): array
    {
        if (! $hotelId) {
            return GuestLayoutConfig::defaults();
        }
        $meta = HotelSettings::where('hotel_id', $hotelId)->value('metadata');

        return GuestLayoutConfig::normalize(is_array($meta) ? ($meta['guest_layout'] ?? null) : null);
    }

    /** Saves an ALREADY VALIDATED config, leaving every other metadata key untouched. */
    public function save(int $hotelId, array $config): array
    {
        $settings = HotelSettings::firstOrCreate(['hotel_id' => $hotelId]);
        $meta = is_array($settings->metadata) ? $settings->metadata : [];
        $meta['guest_layout'] = GuestLayoutConfig::normalize($config);
        $settings->update(['metadata' => $meta]);

        return $meta['guest_layout'];
    }
}
