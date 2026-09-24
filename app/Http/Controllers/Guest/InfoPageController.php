<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use App\Models\InfoEntry;
use Inertia\Inertia;
use Inertia\Response;

/** Guest "Timing" and "Short Calls" pages - both render the same grouped label/value list. */
class InfoPageController extends Controller
{
    public function timing(): Response
    {
        return $this->render(InfoEntry::KIND_TIMING, __('info.timing_title'), __('info.timing_subtitle'));
    }

    public function shortCalls(): Response
    {
        return $this->render(InfoEntry::KIND_SHORT_CALL, __('info.short_calls_title'), __('info.short_calls_subtitle'));
    }

    private function render(string $kind, string $title, string $subtitle): Response
    {
        // groupBy keeps first-appearance order, so admin ordering is preserved
        // both between groups and inside each group.
        $groups = InfoEntry::query()->kind($kind)->ordered()->get()
            ->groupBy(fn (InfoEntry $e) => $e->group ?? '')
            ->map(fn ($items, $name) => [
                'name' => $name !== '' ? $name : null,
                'items' => $items->map(fn (InfoEntry $e) => ['label' => $e->label, 'value' => $e->value])->values(),
            ])
            ->values();

        return Inertia::render('Guest/InfoPage', [
            'kind' => $kind,
            'title' => $title,
            'subtitle' => $subtitle,
            'groups' => $groups,
        ]);
    }
}
