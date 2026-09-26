<?php

namespace App\Services\Layout;

use App\Models\HotelBranch;
use App\Models\HotelSettings;

/** Reads/writes hotel and branch guest layouts. */
class GuestLayoutStore
{
    /**
     * Resolves layout for a hotel, optionally scoped/overridden by a specific branch.
     */
    public function forHotel(?int $hotelId, ?int $branchId = null): array
    {
        $hotelConfig = $this->resolveHotelMasterConfig($hotelId);

        if (! $branchId) {
            return $hotelConfig;
        }

        $branch = HotelBranch::withoutGlobalScope('hotel')->find($branchId);
        if (! $branch) {
            return $hotelConfig;
        }

        $branchMeta = $branch->metadata;
        if (is_array($branchMeta) && ! empty($branchMeta['guest_layout'])) {
            return GuestLayoutConfig::normalize($branchMeta['guest_layout']);
        }

        if (! empty($branch->guest_layout)) {
            $branchConfig = $hotelConfig;
            $branchConfig['template'] = $branch->guest_layout;

            return $branchConfig;
        }

        return $hotelConfig;
    }

    /**
     * Check if a branch has its own explicit layout customization.
     */
    public function hasCustomBranchLayout(int $branchId): bool
    {
        $branch = HotelBranch::withoutGlobalScope('hotel')->find($branchId);
        if (! $branch) {
            return false;
        }

        $branchMeta = $branch->metadata;

        return (is_array($branchMeta) && ! empty($branchMeta['guest_layout'])) || ! empty($branch->guest_layout);
    }

    /**
     * Resolves the master hotel config only.
     */
    protected function resolveHotelMasterConfig(?int $hotelId): array
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

    /** Saves an ALREADY VALIDATED config for the hotel master, leaving other metadata untouched. */
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

    /** Saves an ALREADY VALIDATED config for a specific branch. */
    public function saveForBranch(int $branchId, array $config): array
    {
        $branch = HotelBranch::withoutGlobalScope('hotel')->findOrFail($branchId);
        $meta = is_array($branch->metadata) ? $branch->metadata : [];
        $normalized = GuestLayoutConfig::normalize($config);
        $meta['guest_layout'] = $normalized;
        $branch->update([
            'metadata' => $meta,
            'guest_layout' => $normalized['template'] ?? $branch->guest_layout,
        ]);

        return $meta['guest_layout'];
    }

    /** Resets branch-specific layout so it inherits the master hotel layout. */
    public function resetForBranch(int $branchId): void
    {
        $branch = HotelBranch::withoutGlobalScope('hotel')->findOrFail($branchId);
        $meta = is_array($branch->metadata) ? $branch->metadata : [];
        unset($meta['guest_layout']);
        $branch->update([
            'metadata' => $meta,
            'guest_layout' => null,
        ]);
    }
}
