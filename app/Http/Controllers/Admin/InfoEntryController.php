<?php

namespace App\Http\Controllers\Admin;

use App\Actions\InfoEntries\SaveInfoEntriesAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SaveInfoEntriesRequest;
use App\Models\InfoEntry;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

/**
 * One controller, two admin screens (Timing and Short Calls), told apart by
 * the `kind` route default. Both are the same "edit the whole list" screen.
 */
class InfoEntryController extends Controller
{
    private const KINDS = [
        InfoEntry::KIND_TIMING => [
            'title' => 'Timing',
            'description' => 'Times guests ask about: check-in, check-out, pool, restaurants, spa... Entries with the same group are shown together.',
            'has_group' => true,
            'group_placeholder' => 'e.g. Hotel, Pool, Dining',
            'label_placeholder' => 'e.g. Check-in',
            'value_placeholder' => 'e.g. 15:00  or  07:00 - 20:00',
            'save_url' => '/admin/timing',
        ],
        InfoEntry::KIND_SHORT_CALL => [
            'title' => 'Short Calls',
            'description' => 'Numbers guests dial from the room phone, e.g. IT Support = 15, Helper = 59.',
            'has_group' => false,
            'group_placeholder' => '',
            'label_placeholder' => 'e.g. IT Support',
            'value_placeholder' => 'e.g. 15',
            'save_url' => '/admin/short-calls',
        ],
    ];

    public function edit(string $kind): Response
    {
        abort_unless(isset(self::KINDS[$kind]), 404);

        $kindConfig = self::KINDS[$kind];
        $prefix = $kind === InfoEntry::KIND_SHORT_CALL ? 'short_calls' : $kind;
        $kindConfig['title'] = __("info.{$prefix}_title");
        $kindConfig['description'] = __("info.{$prefix}_desc");
        if (! empty($kindConfig['group_placeholder'])) {
            $kindConfig['group_placeholder'] = __("info.{$prefix}_group_placeholder");
        }
        $kindConfig['label_placeholder'] = __("info.{$prefix}_label_placeholder");
        $kindConfig['value_placeholder'] = __("info.{$prefix}_value_placeholder");

        return Inertia::render('Admin/InfoEntries/Edit', [
            ...$kindConfig,
            'entries' => InfoEntry::query()->kind($kind)->ordered()->get()
                ->map(fn (InfoEntry $e) => [
                    ...$e->only(['id', 'group', 'label', 'value']),
                    'translations' => $e->getTranslationsGrouped(),
                    'label_ar' => $e->getTranslationData('ar')['label'] ?? '',
                    'value_ar' => $e->getTranslationData('ar')['value'] ?? '',
                    'group_ar' => $e->getTranslationData('ar')['group'] ?? '',
                ])
                ->values(),
        ]);
    }

    public function update(SaveInfoEntriesRequest $request, SaveInfoEntriesAction $action, string $kind): RedirectResponse
    {
        abort_unless(isset(self::KINDS[$kind]), 404);

        $action->execute($kind, $request->validated('entries'));

        return back()->with('success', __('admin.messages.saved'));
    }
}
