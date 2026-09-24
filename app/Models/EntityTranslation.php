<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class EntityTranslation extends Model
{
    protected $fillable = [
        'translatable_type',
        'translatable_id',
        'locale',
        'data',
    ];

    protected $casts = [
        'data' => 'array',
    ];

    public function translatable(): MorphTo
    {
        return $this->morphTo();
    }
}
