<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreFacilityRequest;
use App\Models\Facility;
use Illuminate\Http\RedirectResponse;
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
    public function index(): Response
    {
        $this->authorize('viewAny', Facility::class);

        return Inertia::render('Admin/Facilities/Index', [
            'facilities' => Facility::query()
                ->ordered()
                ->with('cover')
                ->paginate(20)
                ->through(fn (Facility $f) => [
                    ...$f->only(['id', 'name', 'slug', 'category', 'status', 'featured', 'sort_order']),
                    'cover_image_url' => $f->cover_image_url,
                ]),
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Facility::class);

        return Inertia::render('Admin/Facilities/Edit', [
            'facility' => null,
        ]);
    }

    public function store(StoreFacilityRequest $request): RedirectResponse
    {
        Facility::create($request->validated());

        return redirect()->route('admin.facilities.index')
            ->with('success', 'Facility created.');
    }

    public function edit(Facility $facility): Response
    {
        $this->authorize('update', $facility);

        return Inertia::render('Admin/Facilities/Edit', [
            'facility' => $facility,
        ]);
    }

    public function update(StoreFacilityRequest $request, Facility $facility): RedirectResponse
    {
        $facility->update($request->validated());

        return redirect()->route('admin.facilities.index')
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
