<?php

namespace App\Actions\Pages;

use App\Models\Page;

class UpdatePageLayoutAction
{
    /**
     * Layout is a display setting, not content - it's stored on the Page
     * itself (not the draft/published PageVersion), so switching it takes
     * effect immediately for both draft and published renders without
     * needing a publish step.
     */
    public function execute(Page $page, string $layout): Page
    {
        $page->update(['layout' => $layout]);

        return $page->fresh();
    }
}
