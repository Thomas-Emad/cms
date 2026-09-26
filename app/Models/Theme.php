<?php

namespace App\Models;

use App\Models\Concerns\BelongsToHotel;
use Illuminate\Database\Eloquent\Model;

class Theme extends Model
{
    use BelongsToHotel;

    protected $fillable = [
        'hotel_id', 'hotel_branch_id', 'name', 'is_active', 'primary_color', 'secondary_color',
        'font_family', 'border_radius', 'button_style', 'card_style', 'config',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'config' => 'array',
    ];

    protected $appends = [
        'header_bg',
        'footer_bg',
    ];

    public function getHeaderBgAttribute(): string
    {
        return $this->config['header_bg'] ?? $this->deriveDarkenedHex($this->primary_color ?? '#059669', 0.35);
    }

    public function getFooterBgAttribute(): string
    {
        return $this->config['footer_bg'] ?? $this->deriveDarkenedHex($this->primary_color ?? '#059669', 0.22);
    }

    /**
     * Shape used to inject CSS variables into the guest layout.
     * Keep keys stable — resources/js/composables/useTheme.ts depends on them.
     */
    public function toCssVariables(): array
    {
        $primary = $this->primary_color ?: '#059669';
        $secondary = $this->secondary_color ?: '#10B981';

        $headerHex = $this->header_bg;
        $footerHex = $this->footer_bg;

        $headerRgba = $this->hexToRgba($headerHex, 0.88);
        $dockRgba = $this->hexToRgba($footerHex, 0.92);

        return [
            '--color-primary' => $primary,
            '--color-secondary' => $secondary,
            '--header-bg' => $headerRgba,
            '--header-bg-solid' => $headerHex,
            '--footer-bg' => $footerHex,
            '--dock-bg' => $dockRgba,
            '--font-family' => $this->font_family,
            '--radius' => match ($this->border_radius) {
                'none' => '0px',
                'small' => '4px',
                'medium' => '8px',
                'large' => '16px',
                'full' => '9999px',
                default => '8px',
            },
        ];
    }

    public function hexToRgba(string $hex, float $alpha = 1.0): string
    {
        if (str_starts_with($hex, 'rgba') || str_starts_with($hex, 'rgb')) {
            return $hex;
        }

        $hex = ltrim($hex, '#');
        if (strlen($hex) === 3) {
            $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
        }
        if (strlen($hex) !== 6) {
            return "rgba(10, 12, 16, {$alpha})";
        }

        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));

        return "rgba({$r}, {$g}, {$b}, {$alpha})";
    }

    public function deriveDarkenedHex(string $hex, float $factor = 0.35): string
    {
        if (str_starts_with($hex, 'rgba') || str_starts_with($hex, 'rgb')) {
            return '#0b0e13';
        }

        $hex = ltrim($hex, '#');
        if (strlen($hex) === 3) {
            $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
        }
        if (strlen($hex) !== 6) {
            return '#0b0e13';
        }

        $r = max(0, min(255, (int) round(hexdec(substr($hex, 0, 2)) * $factor)));
        $g = max(0, min(255, (int) round(hexdec(substr($hex, 2, 2)) * $factor)));
        $b = max(0, min(255, (int) round(hexdec(substr($hex, 4, 2)) * $factor)));

        return sprintf('#%02x%02x%02x', $r, $g, $b);
    }
}
