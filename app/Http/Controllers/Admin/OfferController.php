<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreOfferRequest;
use App\Models\Offer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class OfferController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Offer::class);

        $branchId = $request->integer('branch_id') ?: null;

        return Inertia::render('Admin/Offers/Index', [
            'selected_branch_id' => $branchId,
            'offers' => Offer::query()
                ->when($branchId, fn ($q) => $q->where('hotel_branch_id', $branchId))
                ->latest()
                ->with(['cover', 'branch:id,name,city'])
                ->paginate(20)
                ->withQueryString()
                ->through(fn (Offer $o) => [
                    ...$o->only(['id', 'title', 'slug', 'discount', 'status', 'featured', 'valid_until', 'hotel_branch_id']),
                    'branch' => $o->branch ? ['id' => $o->branch->id, 'name' => $o->branch->name, 'city' => $o->branch->city] : null,
                    'cover_image_url' => $o->cover_image_url,
                ]),
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Offer::class);

        return Inertia::render('Admin/Offers/Edit', ['offer' => null]);
    }

    public function store(StoreOfferRequest $request): RedirectResponse
    {
        Offer::create($request->validated());

        return redirect()->route('admin.offers.index')->with('success', __('admin.messages.offer_created'));
    }

    public function edit(Offer $offer): Response
    {
        $this->authorize('update', $offer);

        return Inertia::render('Admin/Offers/Edit', ['offer' => $offer]);
    }

    public function update(StoreOfferRequest $request, Offer $offer): RedirectResponse
    {
        $offer->update($request->validated());

        return redirect()->route('admin.offers.index')->with('success', __('admin.messages.offer_updated'));
    }

    public function destroy(Offer $offer): RedirectResponse
    {
        $this->authorize('delete', $offer);

        $offer->delete();

        return redirect()->route('admin.offers.index')->with('success', __('admin.messages.offer_deleted'));
    }
}
