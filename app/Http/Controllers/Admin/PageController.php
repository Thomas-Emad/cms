<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Pages\CreatePageAction;
use App\Actions\Pages\PublishPageAction;
use App\Actions\Pages\SaveDraftAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PageSectionsRequest;
use App\Http\Requests\Admin\StorePageRequest;
use App\Models\Page;
use App\Services\PageBuilder\PageRenderService;
use App\Services\PageBuilder\SectionRegistry;
use App\Services\Tenancy\CurrentHotel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PageController extends Controller
{
    /**
     * Tenant-scoped implicitly via Page's BelongsToHotel global scope -
     * no manual hotel_id filter needed here, same as every other admin
     * index in this project (FacilityController::index, etc.).
     */
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Page::class);

        $pages = Page::query()
            ->when($request->string('q')->value(), function ($query, $search) {
                $query->where(fn ($q) => $q
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%"));
            })
            ->orderByDesc('updated_at')
            ->paginate(20)
            ->withQueryString()
            ->through(fn (Page $page) => $page->only([
                'id', 'name', 'slug', 'status', 'is_home', 'published_version_id', 'updated_at',
            ]));

        return Inertia::render('Admin/Pages/Index', [
            'pages' => $pages,
            'filters' => ['q' => $request->string('q')->value()],
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Page::class);

        return Inertia::render('Admin/Pages/Create');
    }

    /**
     * Delegates entirely to the existing CreatePageAction - this method
     * is just the HTTP boundary (validate, resolve the current hotel,
     * call the action, redirect). No page-creation logic is duplicated
     * here.
     */
    public function store(StorePageRequest $request, CreatePageAction $action, CurrentHotel $currentHotel): RedirectResponse
    {
        $page = $action->execute($currentHotel->get(), $request->validated());

        return redirect()
            ->route('admin.pages.builder', $page)
            ->with('success', 'Page created. Start building below.');
    }

    /**
     * Loads the Builder shell for an existing draft page. Only the
     * draft is ever readable here - there is no route parameter or code
     * path in this controller that lets the Builder load the published
     * version instead (that's Guest\PageController's job, and only its
     * job).
     */
    public function edit(Page $page): Response
    {
        $this->authorize('update', $page);

        $draft = $page->draftVersion()->firstOrFail();

        return Inertia::render('Admin/Pages/Builder', [
            'page' => $page->only(['id', 'name', 'slug', 'status', 'published_version_id']),
            'schemaVersion' => $draft->sections['schema_version'] ?? 1,
            'sections' => $draft->sections['sections'] ?? [],
            'availableSectionTypes' => SectionRegistry::types(),
        ]);
    }

    public function updateDraft(PageSectionsRequest $request, Page $page, SaveDraftAction $action): RedirectResponse
    {
        $action->execute($page, $request->validated('sections'));

        return back()->with('success', 'Draft saved.');
    }

    public function publish(Page $page, PublishPageAction $action): RedirectResponse
    {
        $this->authorize('publish', $page);

        $action->execute($page);

        return redirect()
            ->route('admin.pages.index')
            ->with('success', 'Page published.');
    }

    /**
     * Full-page preview: resolves the CURRENT DRAFT (already saved)
     * through the exact same PageRenderService the guest site uses, and
     * renders it through the exact same SectionRenderer.vue - just fed
     * draft data instead of published data. No second rendering system.
     */
    public function preview(Page $page, PageRenderService $renderService, CurrentHotel $currentHotel): Response
    {
        $this->authorize('view', $page);

        $draft = $page->draftVersion()->firstOrFail();

        $sections = $renderService->resolveSections(
            $draft->sections['sections'] ?? [],
            $currentHotel->get()
        );

        return Inertia::render('Admin/Pages/Preview', [
            'page' => $page->only(['id', 'name', 'slug']),
            'sections' => $sections,
        ]);
    }

    /**
     * Live in-canvas preview resolution (unchanged from earlier
     * checkpoints). Accepts a single section's CURRENT (possibly
     * unsaved) props and returns freshly resolved data.
     */
    public function resolvePreviewSection(
        PageSectionsRequest $request,
        Page $page,
        PageRenderService $renderService,
        CurrentHotel $currentHotel
    ): JsonResponse {
        $this->authorize('update', $page);

        $sections = $request->validated('sections');

        abort_unless(count($sections) === 1, 422, 'resolvePreviewSection expects exactly one section.');

        $resolved = $renderService->resolveOne($sections[0], $currentHotel->get());

        return response()->json(['section' => $resolved]);
    }
}
