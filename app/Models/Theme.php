<?php

namespace App\Models;

use App\Models\Concerns\BelongsToHotel;
use Illuminate\Database\Eloquent\Model;

class Theme extends Model
{
    use BelongsToHotel;

    protected $fillable = [
        'hotel_id', 'name', 'is_active', 'primary_color', 'secondary_color',
        'font_family', 'border_radius', 'button_style', 'card_style', 'config',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'config' => 'array',
    ];

    /**
     * Shape used to inject CSS variables into the guest layout.
     * Keep keys stable — resources/js/composables/useTheme.ts depends on them.
     */
    public function toCssVariables(): array
    {
        return [
            '--color-primary' => $this->primary_color,
            '--color-secondary' => $this->secondary_color,
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
}
