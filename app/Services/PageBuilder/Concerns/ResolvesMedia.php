<?php

namespace App\Services\PageBuilder\Concerns;

use App\Models\Hotel;
use App\Models\Media;

/**
 * Shared by ImageSectionDefinition and GallerySectionDefinition (and any
 * future section that references media by id). A section's JSON only
 * ever stores a media_id/media_ids reference (see Phase 3 design doc
 * §10) - never a URL, never embedded media content - so resolving that
 * reference to an actual URL at render time is what every media-bearing
 * section needs, and it needs the SAME tenant-safety check every time:
 * re-validate the media row's hotel_id against the current hotel rather
 * than trusting that a stored id still belongs to this tenant (props
 * schema validation with `exists:media,id` only proves the row exists
 * SOMEWHERE, not that it belongs to this hotel - a page's JSON could in
 * theory be tampered with or copied across a tenant boundary elsewhere in
 * the system, so this check is not redundant with schema validation).
 */
trait ResolvesMedia
{
    /**
     * @return array{url: string, alt_text: ?string}|null Null if the id is
     *                                                    missing, doesn't exist, or belongs to a different hotel.
     */
    protected function resolveMedia(?int $mediaId, Hotel $hotel): ?array
    {
        if ($mediaId === null) {
            return null;
        }

        $media = Media::query()
            ->where('id', $mediaId)
            ->where('hotel_id', $hotel->id) // tenant-safety re-check, not optional
            ->first();

        if ($media === null) {
            return null;
        }

        return ['url' => $media->url, 'alt_text' => $media->alt_text];
    }

    /**
     * Same tenant-safety guarantee as resolveMedia(), for a list of ids.
     * Order is preserved to match the order the admin arranged the
     * gallery in; ids that don't resolve (deleted, wrong tenant) are
     * silently dropped rather than breaking the whole section - a guest
     * seeing 4 images instead of 5 because one was deleted is much better
     * than a 500 error on the whole page.
     *
     * @param  int[]  $mediaIds
     * @return array<array{url: string, alt_text: ?string}>
     */
    protected function resolveMediaList(array $mediaIds, Hotel $hotel): array
    {
        $mediaById = Media::query()
            ->whereIn('id', $mediaIds)
            ->where('hotel_id', $hotel->id)
            ->get()
            ->keyBy('id');

        $resolved = [];
        foreach ($mediaIds as $id) {
            if ($mediaById->has($id)) {
                $media = $mediaById->get($id);
                $resolved[] = ['url' => $media->url, 'alt_text' => $media->alt_text];
            }
        }

        return $resolved;
    }
}
