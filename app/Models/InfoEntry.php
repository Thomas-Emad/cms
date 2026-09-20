<?php

namespace App\Models;

use App\Models\Concerns\BelongsToHotel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class InfoEntry extends Model
{
    use BelongsToHotel;

    public const KIND_TIMING = 'timing';
    public const KIND_SHORT_CALL = 'short_call';

    protected $fillable = ['hotel_id', 'kind', 'group', 'label', 'value', 'sort_order'];

    public function scopeKind(Builder $query, string $kind): Builder
    {
        return $query->where('kind', $kind);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }
}
