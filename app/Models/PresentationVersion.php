<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PresentationVersion extends Model
{
    protected $fillable = ['entity_presentation_id', 'sections', 'state', 'published_at', 'published_by'];

    protected $casts = [
        'sections' => 'array',
        'published_at' => 'datetime',
    ];

    public function presentation(): BelongsTo
    {
        return $this->belongsTo(EntityPresentation::class, 'entity_presentation_id');
    }
}
