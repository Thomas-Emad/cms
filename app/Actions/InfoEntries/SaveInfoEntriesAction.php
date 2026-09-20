<?php

namespace App\Actions\InfoEntries;

use App\Models\InfoEntry;
use Illuminate\Support\Facades\DB;

/**
 * "Replace the whole list" save, same idea as UpdateMenuAction: the admin
 * edits the entire list on one screen and submits it in order. Rows with a
 * known `id` are updated, rows without one are created, and any existing
 * row missing from the submission is deleted. Order in the payload becomes
 * sort_order. One transaction, so a guest never sees a half-saved list.
 *
 * Tenant safety: InfoEntry is BelongsToHotel, so the lookup below only
 * ever sees the current hotel's rows - an `id` from another hotel simply
 * isn't found and is treated as a new row.
 */
class SaveInfoEntriesAction
{
    /** @param array<int, array{id?: int|null, group?: string|null, label: string, value: string}> $entries */
    public function execute(string $kind, array $entries): void
    {
        DB::transaction(function () use ($kind, $entries) {
            $existing = InfoEntry::query()->kind($kind)->get()->keyBy('id');
            $keep = [];

            foreach (array_values($entries) as $position => $row) {
                $attributes = [
                    // Only timing entries are grouped; short calls are a flat list.
                    'group' => $kind === InfoEntry::KIND_TIMING ? (filled($row['group'] ?? null) ? trim($row['group']) : null) : null,
                    'label' => trim($row['label']),
                    'value' => trim($row['value']),
                    'sort_order' => $position,
                ];

                $id = $row['id'] ?? null;

                if ($id !== null && $existing->has($id)) {
                    $existing->get($id)->update($attributes);
                    $keep[] = $id;
                } else {
                    $keep[] = InfoEntry::create($attributes + ['kind' => $kind])->id;
                }
            }

            InfoEntry::query()->kind($kind)->whereNotIn('id', $keep)->delete();
        });
    }
}
