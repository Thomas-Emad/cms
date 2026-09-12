<?php

namespace App\Actions\Pages;

use App\Models\Page;
use App\Services\PageBuilder\SectionsValidator;

class SaveDraftAction
{
    public function __construct(private SectionsValidator $validator)
    {
    }

    /**
     * Mutates the existing draft_version_id row in place - this is the
     * "same draft row, continuously edited" behavior from the Phase 3
     * design doc §1. Never creates a new PageVersion; that only happens
     * on publish.
     */
    public function execute(Page $page, array $sections): void
    {
        $this->validator->validate($sections);

        $page->draftVersion()->update([
            'sections' => ['schema_version' => 1, 'sections' => $sections],
        ]);
    }
}
