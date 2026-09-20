<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreFacilityRequest;
use App\Models\Facility;
use App\Models\Media;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Reference CRUD pattern for tenant-scoped content controllers.
 * RestaurantController, ServiceController, EventController, OfferController,
 * and ExperienceController should follow this same shape:
 *   - index: paginated list, tenant-scoped automatically via BelongsToHotel
 *   - create/store: form + validated persist
 *   - edit/update: form + validated persist
 *   - destroy: policy-gated delete
 */
class FacilityController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Facility::class);

        // ?category=meeting powers the "Meeting Rooms" sidebar entry: same data, filtered list.
        $category = $request->string('category')->value() ?: null;

        return Inertia::render('Admin/Facilities/Index', [
            'category' => $category,
            'facilities' => Facility::query()
                ->category($category)
                ->ordered()
                ->with('cover')
                ->paginate(20)
                ->withQueryString()
                ->through(fn (Facility $f) => [
                    ...$f->only(['id', 'name', 'slug', 'category', 'status', 'featured', 'sort_order']),
                    'cover_image_url' => $f->cover_image_url,
                ]),
        ]);
    }

    public function create(Request $request): Response
    {
        $this->authorize('create', Facility::class);

        return Inertia::render('Admin/Facilities/Edit', [
            'facility' => null,
            'default_category' => $request->string('category')->value() ?: null,
        ]);
    }

    public function store(StoreFacilityRequest $request): RedirectResponse
    {
        $facility = Facility::create($request->validated());

        // Straight to the edit screen so photos can be added right away.
        return redirect()->route('admin.facilities.edit', $facility)
            ->with('success', 'Facility created. You can now add photos.');
    }

    public function edit(Facility $facility): Response
    {
        $this->authorize('update', $facility);

        return Inertia::render('Admin/Facilities/Edit', [
            'facility' => $facility,
            'cover' => $facility->cover ? [$facility->cover->toPayload()] : [],
            'gallery' => $facility->gallery->map(fn (Media $m) => $m->toPayload())->values(),
        ]);
    }

    public function update(StoreFacilityRequest $request, Facility $facility): RedirectResponse
    {
        $facility->update($request->validated());

        return redirect()->route('admin.facilities.edit', $facility)
            ->with('success', 'Facility updated.');
    }

    public function destroy(Facility $facility): RedirectResponse
    {
        $this->authorize('delete', $facility);

        $facility->delete();

        return redirect()->route('admin.facilities.index')
            ->with('success', 'Facility deleted.');
    }
}
