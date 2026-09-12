<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Append-only by convention: a 'published' row is never mutated after
 * creation (that's what makes it a reliable revision log - see
 * PublishPageAction). The 'draft' row IS mutated in place on every
 * autosave; it is the one exception to "append-only" and is intentional
 * (see Phase 3 design doc §1 and §9 for why draft autosaves aren't
 * versioned individually).
 */
class PageVersion extends Model
{
    protected $fillable = [
        'page_id', 'sections', 'state', 'published_at', 'published_by',
    ];

    protected $casts = [
        'sections' => 'array',
        'published_at' => 'datetime',
    ];

    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }

    public function publisher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'published_by');
    }
}
