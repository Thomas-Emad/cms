<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreHotelBranchRequest;
use App\Models\HotelBranch;
use App\Models\Media;
use App\Services\Tenancy\CurrentHotel;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class BranchController extends Controller
{
    public function index(): Response
    {
        $this->authorize('viewAny', HotelBranch::class);

        return Inertia::render('Admin/Branches/Index', [
            'branches' => HotelBranch::query()
                ->ordered()
                ->with('cover')
                ->paginate(20)
                ->through(fn (HotelBranch $b) => [
                    'id' => $b->id,
                    'name' => $b->getRawOriginal('name') ?? $b->name,
                    'slug' => $b->slug,
                    'domain' => $b->domain,
                    'city' => $b->getRawOriginal('city') ?? $b->city,
                    'address' => $b->getRawOriginal('address') ?? $b->address,
                    'phone' => $b->phone,
                    'email' => $b->email,
                    'status' => $b->status,
                    'is_main' => (bool) $b->is_main,
                    'sort_order' => $b->sort_order,
                    'cover_image_url' => $b->cover_image_url,
                    'all_photos_count' => count($b->all_photos),
                ]),
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', HotelBranch::class);

        return Inertia::render('Admin/Branches/Edit', [
            'branch' => null,
            'cover' => [],
            'gallery' => [],
        ]);
    }

    public function store(StoreHotelBranchRequest $request, CurrentHotel $currentHotel): RedirectResponse
    {
        $data = $request->validated();
        $hotelId = $currentHotel->id();
        $data['hotel_id'] = $hotelId;

        if (! empty($data['is_main'])) {
            HotelBranch::where('hotel_id', $hotelId)->update(['is_main' => false]);
        }

        $branch = HotelBranch::create($data);

        return redirect()->route('admin.branches.edit', $branch)
            ->with('success', __('admin.messages.branch_created'));
    }

    public function edit(HotelBranch $branch): Response
    {
        $this->authorize('update', $branch);

        return Inertia::render('Admin/Branches/Edit', [
            'branch' => [
                ...$branch->toArray(),
                'name' => $branch->getRawOriginal('name') ?? $branch->name,
                'city' => $branch->getRawOriginal('city') ?? $branch->city,
                'address' => $branch->getRawOriginal('address') ?? $branch->address,
                'short_description' => $branch->getRawOriginal('short_description') ?? $branch->short_description,
                'description' => $branch->getRawOriginal('description') ?? $branch->description,
                'translations_data' => $branch->translations_data,
            ],
            'cover' => $branch->cover ? [$branch->cover->toPayload()] : [],
            'gallery' => $branch->gallery->map(fn (Media $m) => $m->toPayload())->values(),
        ]);
    }

    public function update(StoreHotelBranchRequest $request, HotelBranch $branch, CurrentHotel $currentHotel): RedirectResponse
    {
        $this->authorize('update', $branch);
        $data = $request->validated();

        if (! empty($data['is_main'])) {
            HotelBranch::where('hotel_id', $currentHotel->id())
                ->where('id', '!=', $branch->id)
                ->update(['is_main' => false]);
        }

        $branch->update($data);

        return redirect()->route('admin.branches.edit', $branch)
            ->with('success', __('admin.messages.branch_updated'));
    }

    public function destroy(HotelBranch $branch): RedirectResponse
    {
        $this->authorize('delete', $branch);
        $branch->delete();

        return redirect()->route('admin.branches.index')
            ->with('success', __('admin.messages.branch_deleted'));
    }
}
