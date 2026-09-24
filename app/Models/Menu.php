<?php

namespace App\Models;

use App\Models\Concerns\FormatsDates;
use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Menu extends Model
{
    use FormatsDates, HasTranslations;

    protected array $translatable = ['name'];

    protected $fillable = ['restaurant_id', 'name', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function categories(): HasMany
    {
        return $this->hasMany(MenuCategory::class)->orderBy('sort_order');
    }
}
