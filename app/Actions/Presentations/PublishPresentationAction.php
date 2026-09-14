<?php

namespace App\Actions\Presentations;

use App\Models\EntityPresentation;
use App\Models\PresentationVersion;
use Illuminate\Support\Facades\DB;

class PublishPresentationAction
{
    public function execute(EntityPresentation $presentation): PresentationVersion
    {
        return DB::transaction(function () use ($presentation) {
            $draft = $presentation->draftVersion()->firstOrFail();

            $published = PresentationVersion::create([
                'entity_presentation_id' => $presentation->id,
                'sections' => $draft->sections,
                'state' => 'published',
                'published_at' => now(),
                'published_by' => auth()->id(),
            ]);

            $presentation->update([
                'published_version_id' => $published->id,
                'status' => 'published',
            ]);

            return $published;
        });
    }
}
