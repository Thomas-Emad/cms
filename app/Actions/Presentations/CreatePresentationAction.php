<?php

namespace App\Actions\Presentations;

use App\Models\EntityPresentation;
use App\Models\Hotel;
use App\Models\PresentationVersion;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CreatePresentationAction
{
    /**
     * Idempotent: "Customize Guest Page" is always a safe click. If a
     * presentation already exists for this entity, return it (with its
     * existing draft) rather than erroring or creating a duplicate - the
     * unique(presentable_type, presentable_id) constraint would reject a
     * second row anyway, but checking first avoids relying on catching
     * that as control flow.
     */
    public function firstOrCreate(Hotel $hotel, Model $entity, array $initialSections = []): EntityPresentation
    {
        $existing = EntityPresentation::query()
            ->where('presentable_type', $entity::class)
            ->where('presentable_id', $entity->getKey())
            ->first();

        if ($existing) {
            return $existing;
        }

        return DB::transaction(function () use ($hotel, $entity, $initialSections) {
            $presentation = EntityPresentation::create([
                'hotel_id' => $hotel->id,
                'presentable_type' => $entity::class,
                'presentable_id' => $entity->getKey(),
                'status' => 'draft',
            ]);

            $draft = PresentationVersion::create([
                'entity_presentation_id' => $presentation->id,
                'sections' => ['schema_version' => 1, 'sections' => $initialSections],
                'state' => 'draft',
            ]);

            $presentation->update(['draft_version_id' => $draft->id]);

            return $presentation->fresh();
        });
    }
}
