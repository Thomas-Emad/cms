<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Media extends Model
{
    protected $table = 'media';

    protected $fillable = [
        'hotel_id', 'disk', 'path', 'mime_type', 'size', 'alt_text',
        'mediable_type', 'mediable_id', 'collection', 'sort_order',
    ];

    public function mediable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Supports both real uploaded files (`disk` + `path` resolved via
     * Storage) and external URLs stored directly in `path` (used by demo
     * seed data, and useful later for e.g. importing image URLs from PMS
     * feeds without downloading them first).
     */
    public function getUrlAttribute(): string
    {
        if (str_starts_with($this->path, 'http://') || str_starts_with($this->path, 'https://')) {
            return $this->path;
        }

        return \Illuminate\Support\Facades\Storage::disk($this->disk)->url($this->path);
    }

    /** Shape sent to the admin MediaManager and guest galleries. */
    public function toPayload(): array
    {
        return ['id' => $this->id, 'url' => $this->url, 'alt_text' => $this->alt_text, 'type' => $this->isVideo() ? 'video' : 'image'];
    }

    /** Uploads store a mime type; seeded/external media may not, so fall back to the file extension. */
    public function isVideo(): bool
    {
        if ($this->mime_type !== null) {
            return str_starts_with($this->mime_type, 'video/');
        }

        return (bool) preg_match('/\.(mp4|webm|mov)(\?.*)?$/i', (string) $this->path);
    }
}
