<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreExperienceRequest;
use App\Models\Experience;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ExperienceController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Experience::class);

        $branchId = $request->integer('branch_id') ?: null;

        return Inertia::render('Admin/Experiences/Index', [
            'selected_branch_id' => $branchId,
            'experiences' => Experience::query()
                ->when($branchId, fn ($q) => $q->where('hotel_branch_id', $branchId))
                ->ordered()
                ->with(['cover', 'branch:id,name,city'])
                ->paginate(20)
                ->withQueryString()
                ->through(fn (Experience $e) => [
                    ...$e->only(['id', 'title', 'slug', 'category', 'status', 'featured', 'hotel_branch_id']),
                    'branch' => $e->branch ? ['id' => $e->branch->id, 'name' => $e->branch->name, 'city' => $e->branch->city] : null,
                    'cover_image_url' => $e->cover_image_url,
                ]),
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Experience::class);

        return Inertia::render('Admin/Experiences/Edit', ['experience' => null]);
    }

    public function store(StoreExperienceRequest $request): RedirectResponse
    {
        Experience::create($request->validated());

        return redirect()->route('admin.experiences.index')->with('success', __('admin.messages.experience_created'));
    }

    public function edit(Experience $experience): Response
    {
        $this->authorize('update', $experience);

        return Inertia::render('Admin/Experiences/Edit', ['experience' => $experience]);
    }

    public function update(StoreExperienceRequest $request, Experience $experience): RedirectResponse
    {
        $experience->update($request->validated());

        return redirect()->route('admin.experiences.index')->with('success', __('admin.messages.experience_updated'));
    }

    public function destroy(Experience $experience): RedirectResponse
    {
        $this->authorize('delete', $experience);

        $experience->delete();

        return redirect()->route('admin.experiences.index')->with('success', __('admin.messages.experience_deleted'));
    }
}
