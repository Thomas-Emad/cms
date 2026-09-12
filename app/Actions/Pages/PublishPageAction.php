<?php

namespace App\Actions\Pages;

use App\Models\Page;
use App\Models\PageVersion;
use Illuminate\Support\Facades\DB;

class PublishPageAction
{
    /**
     * Snapshots the current draft's sections into a brand-new PageVersion
     * row (state=published), then flips pages.published_version_id to it.
     * The draft row itself is never touched by this action - it remains
     * the working copy, now equal in content to what was just published,
     * ready for the next round of edits (see Phase 3 design doc §1).
     *
     * Atomicity: the transaction guarantees a guest request either reads
     * the old published_version_id or the new one - never a state where
     * a new PageVersion row exists but the pointer hasn't caught up, and
     * never a state where the pointer points at a row that isn't fully
     * written yet.
     */
    public function execute(Page $page): PageVersion
    {
        return DB::transaction(function () use ($page) {
            $draft = $page->draftVersion()->firstOrFail();

            $published = PageVersion::create([
                'page_id' => $page->id,
                'sections' => $draft->sections,
                'state' => 'published',
                'published_at' => now(),
                'published_by' => auth()->id(),
            ]);

            $page->update([
                'published_version_id' => $published->id,
                'status' => 'published',
            ]);

            return $published;
        });
    }
}
