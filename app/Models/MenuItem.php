<?php

namespace App\Models;

use App\Models\Concerns\HasMedia;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MenuItem extends Model
{
    use HasMedia;

    protected $fillable = [
        'menu_category_id', 'name', 'description', 'price',
        'dietary_info', 'is_available', 'sort_order',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'dietary_info' => 'array',
        'is_available' => 'boolean',
    ];

    protected $appends = ['cover_image_url'];

    public function category(): BelongsTo
    {
        return $this->belongsTo(MenuCategory::class, 'menu_category_id');
    }
}
