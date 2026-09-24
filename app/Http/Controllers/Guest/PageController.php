<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Services\PageBuilder\PageRenderService;
use App\Services\Tenancy\CurrentHotel;
use Inertia\Inertia;
use Inertia\Response;

class PageController extends Controller
{
    public function __construct(private PageRenderService $renderService) {}

    /**
     * Guests only ever reach this via published_version_id - there is no
     * guest-facing route that can read a draft, by construction (this
     * controller doesn't accept a version parameter at all).
     */
    public function home(CurrentHotel $currentHotel): Response
    {
        $page = Page::query()->published()->home()->firstOrFail();

        return $this->renderPage($page, $currentHotel);
    }

    public function show(string $slug, CurrentHotel $currentHotel): Response
    {
        $page = Page::query()->published()->where('slug', $slug)->firstOrFail();

        return $this->renderPage($page, $currentHotel);
    }

    private function renderPage(Page $page, CurrentHotel $currentHotel): Response
    {
        $version = $page->publishedVersion;

        // Defensive: status=published with a null publishedVersion
        // shouldn't be reachable given Page::scopePublished()'s
        // whereNotNull check, but a 404 here is still preferable to a
        // null-pointer error if that invariant is ever violated.
        abort_if($version === null, 404);

        $sections = $this->renderService->resolveSections(
            $version->sections['sections'] ?? [],
            $currentHotel->get()
        );

        return Inertia::render('Guest/PageView', [
            'page' => $page->only(['id', 'name', 'slug', 'layout', 'seo_title', 'seo_description']),
            'sections' => $sections,
        ]);
    }
}
