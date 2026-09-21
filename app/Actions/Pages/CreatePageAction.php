<?php

namespace App\Actions\Pages;

use App\Models\Hotel;
use App\Models\Page;
use App\Models\PageVersion;
use Illuminate\Support\Facades\DB;

class CreatePageAction
{
    /**
     * @param array{name: string, slug: string, is_home?: bool, layout?: string} $attributes
     */
    public function execute(Hotel $hotel, array $attributes, array $initialSections = []): Page
    {
        return DB::transaction(function () use ($hotel, $attributes, $initialSections) {
            if (! empty($attributes['is_home'])) {
                $this->demoteExistingHomePage($hotel);
            }

            $page = Page::create([
                'hotel_id' => $hotel->id,
                'name' => $attributes['name'],
                'slug' => $attributes['slug'],
                'is_home' => $attributes['is_home'] ?? false,
                'layout' => $attributes['layout'] ?? 'scroll',
                'status' => 'draft',
            ]);

            $draft = PageVersion::create([
                'page_id' => $page->id,
                'sections' => ['schema_version' => 1, 'sections' => $initialSections],
                'state' => 'draft',
            ]);

            // draft_version_id is set exactly once, here, at creation time,
            // and is never reassigned - see Page model docblock / Phase 3
            // design doc §1. It's mutated in place from now on.
            $page->update(['draft_version_id' => $draft->id]);

            return $page->fresh();
        });
    }

    /**
     * Friendly application-level UX: setting a new home page auto-demotes
     * the old one rather than making the admin do it as a separate step.
     * This is a courtesy, not the safety mechanism - the pages.home_marker
     * unique index (see migration) is what actually prevents two home
     * pages existing under any circumstance, including races this
     * transaction doesn't anticipate.
     */
    private function demoteExistingHomePage(Hotel $hotel): void
    {
        Page::where('hotel_id', $hotel->id)->where('is_home', true)->update(['is_home' => false]);
    }
}
