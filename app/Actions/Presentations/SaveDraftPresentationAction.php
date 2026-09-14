<?php

namespace App\Actions\Presentations;

use App\Models\EntityPresentation;
use App\Services\PageBuilder\SectionsValidator;

class SaveDraftPresentationAction
{
    public function __construct(private SectionsValidator $validator)
    {
    }

    public function execute(EntityPresentation $presentation, array $sections): void
    {
        $this->validator->validate($sections);

        $presentation->draftVersion()->update([
            'sections' => ['schema_version' => 1, 'sections' => $sections],
        ]);
    }
}
