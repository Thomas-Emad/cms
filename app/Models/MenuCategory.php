<?php

namespace App\Models;

use App\Models\Concerns\FormatsDates;
use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MenuCategory extends Model
{
    use FormatsDates, HasTranslations;

    protected array $translatable = ['name'];

    protected $fillable = ['menu_id', 'name', 'sort_order'];

    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(MenuItem::class)->orderBy('sort_order');
    }
}
