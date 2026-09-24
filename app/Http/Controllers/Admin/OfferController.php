<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreOfferRequest;
use App\Models\Offer;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class OfferController extends Controller
{
    public function index(): Response
    {
        $this->authorize('viewAny', Offer::class);

        return Inertia::render('Admin/Offers/Index', [
            'offers' => Offer::query()->latest()->with('cover')->paginate(20)
                ->through(fn (Offer $o) => [
                    ...$o->only(['id', 'title', 'slug', 'discount', 'status', 'featured', 'valid_until']),
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
