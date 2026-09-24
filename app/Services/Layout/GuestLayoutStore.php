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
        $settings = HotelSettings::where('hotel_id', $hotelId)->first();
        if (! $settings) {
            return GuestLayoutConfig::defaults();
        }
        $meta = $settings->metadata;
        $config = GuestLayoutConfig::normalize(is_array($meta) ? ($meta['guest_layout'] ?? null) : null);

        if ($settings->guest_view && empty($meta['guest_layout']['template'])) {
            $config['template'] = $settings->guest_view;
        }

        return $config;
    }

    /** Saves an ALREADY VALIDATED config, leaving every other metadata key untouched. */
    public function save(int $hotelId, array $config): array
    {
        $settings = HotelSettings::firstOrCreate(['hotel_id' => $hotelId]);
        $meta = is_array($settings->metadata) ? $settings->metadata : [];
        $normalized = GuestLayoutConfig::normalize($config);
        $meta['guest_layout'] = $normalized;
        $settings->update([
            'metadata' => $meta,
            'guest_view' => $normalized['template'] ?? $settings->guest_view,
        ]);

        return $meta['guest_layout'];
    }
}
