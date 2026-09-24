<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Presentations\CreatePresentationAction;
use App\Actions\Presentations\PublishPresentationAction;
use App\Actions\Presentations\SaveDraftPresentationAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PageSectionsRequest;
use App\Models\Restaurant;
use App\Services\PageBuilder\PageRenderService;
use App\Services\PageBuilder\SectionRegistry;
use App\Services\Tenancy\CurrentHotel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Deliberately reuses RestaurantPolicy (not a new EntityPresentationPolicy)
 * - customizing a restaurant's guest page is an editing action on that
 * restaurant, gated the same way editing its name/description already
 * is. This mirrors AdminPageController almost exactly; the only real
 * difference is CreatePresentationAction::firstOrCreate() replacing
 * CreatePageAction, and passing $restaurant through as render context.
 */
class RestaurantPresentationController extends Controller
{
    public function edit(Restaurant $restaurant, CreatePresentationAction $createAction, CurrentHotel $currentHotel): Response
    {
        $this->authorize('update', $restaurant);

        $presentation = $createAction->firstOrCreate($currentHotel->get(), $restaurant);
        $draft = $presentation->draftVersion;

        return Inertia::render('Admin/Pages/Builder', [
            'page' => [
                'id' => $presentation->id,
                'name' => $restaurant->name,
                'slug' => $restaurant->slug,
                'status' => $presentation->status,
                'published_version_id' => $presentation->published_version_id,
            ],
            'schemaVersion' => $draft->sections['schema_version'] ?? 1,
            'sections' => $draft->sections['sections'] ?? [],
            'availableSectionTypes' => SectionRegistry::typesForContext('restaurant'),
            // Tells the shared Builder shell this is an entity presentation,
            // not a standalone Page - drives the toolbar's context label
            // and which API base URL the store posts to (see store.ts).
            'context' => [
                'type' => 'restaurant',
                'entityName' => $restaurant->name,
                'backLabel' => __('nav.restaurants'),
                'backHref' => '/admin/restaurants',
                'apiBase' => "/admin/restaurants/{$restaurant->id}/presentation",
            ],
        ]);
    }

    public function updateDraft(PageSectionsRequest $request, Restaurant $restaurant, CreatePresentationAction $createAction, SaveDraftPresentationAction $action, CurrentHotel $currentHotel): RedirectResponse
    {
        $this->authorize('update', $restaurant);

        $presentation = $createAction->firstOrCreate($currentHotel->get(), $restaurant);
        $action->execute($presentation, $request->validated('sections'));

        return back()->with('success', __('admin.messages.draft_saved'));
    }

    public function publish(Restaurant $restaurant, CreatePresentationAction $createAction, PublishPresentationAction $action, CurrentHotel $currentHotel): RedirectResponse
    {
        $this->authorize('update', $restaurant);

        $presentation = $createAction->firstOrCreate($currentHotel->get(), $restaurant);
        $action->execute($presentation);

        return redirect()->route('admin.restaurants.index')->with('success', __('admin.messages.restaurant_published'));
    }

    public function preview(Restaurant $restaurant, CreatePresentationAction $createAction, PageRenderService $renderService, CurrentHotel $currentHotel): Response
    {
        $this->authorize('view', $restaurant);

        $presentation = $createAction->firstOrCreate($currentHotel->get(), $restaurant);
        $draft = $presentation->draftVersion;

        $sections = $renderService->resolveSections(
            $draft->sections['sections'] ?? [],
            $currentHotel->get(),
            $restaurant // entity context - this is what makes restaurant-menu etc. resolve correctly
        );

        return Inertia::render('Admin/Pages/Preview', [
            'page' => ['id' => $presentation->id, 'name' => $restaurant->name, 'slug' => $restaurant->slug],
            'sections' => $sections,
            'backToBuilderHref' => "/admin/restaurants/{$restaurant->id}/presentation/builder",
        ]);
    }

    public function resolvePreviewSection(
        PageSectionsRequest $request,
        Restaurant $restaurant,
        PageRenderService $renderService,
        CurrentHotel $currentHotel
    ): JsonResponse {
        $this->authorize('update', $restaurant);

        $sections = $request->validated('sections');
        abort_unless(count($sections) === 1, 422, 'resolvePreviewSection expects exactly one section.');

        $resolved = $renderService->resolveOne($sections[0], $currentHotel->get(), $restaurant);

        return response()->json(['section' => $resolved]);
    }
}
