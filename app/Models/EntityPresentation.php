<?php

namespace App\Models;

use App\Models\Concerns\BelongsToHotel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class EntityPresentation extends Model
{
    use BelongsToHotel;

    protected $fillable = ['hotel_id', 'presentable_type', 'presentable_id', 'status', 'draft_version_id', 'published_version_id'];

    public function presentable(): MorphTo
    {
        return $this->morphTo();
    }

    public function draftVersion(): BelongsTo
    {
        return $this->belongsTo(PresentationVersion::class, 'draft_version_id');
    }

    public function publishedVersion(): BelongsTo
    {
        return $this->belongsTo(PresentationVersion::class, 'published_version_id');
    }
}
