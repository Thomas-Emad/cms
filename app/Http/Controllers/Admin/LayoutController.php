<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Layout\GuestLayoutConfig;
use App\Services\Layout\GuestLayoutStore;
use App\Services\Tenancy\CurrentHotel;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

/** Admin: choose the guest-screen layout template and edit its menu for the hotel or individual branches. */
class LayoutController extends Controller
{
    public function edit(Request $request, GuestLayoutStore $store): Response
    {
        $hotel = app(CurrentHotel::class)->get();
        $this->authorize('view', $hotel);

        $branches = $hotel->branches()->ordered()->get(['id', 'name', 'slug', 'city']);
        $branchId = $request->integer('branch_id') ?: null;

        $selectedBranch = null;
        if ($branchId) {
            $selectedBranch = $branches->firstWhere('id', $branchId);
            if (! $selectedBranch) {
                $branchId = null;
            }
        }

        $hasCustomBranchLayout = $selectedBranch ? $store->hasCustomBranchLayout($selectedBranch->id) : false;

        return Inertia::render('Admin/Layout/Edit', [
            'config' => $store->forHotel($hotel->id, $selectedBranch?->id),
            'defaults' => GuestLayoutConfig::defaults(),
            'branches' => $branches,
            'selected_branch_id' => $selectedBranch?->id,
            'selected_branch' => $selectedBranch,
            'has_custom_branch_layout' => $hasCustomBranchLayout,
            'errors_list' => session()->pull('layout_errors', []),
            'flash_ok' => session()->pull('layout_saved'),
        ]);
    }

    public function update(Request $request, GuestLayoutStore $store): RedirectResponse
    {
        $hotel = app(CurrentHotel::class)->get();
        $this->authorize('update', $hotel);

        $branchId = $request->integer('hotel_branch_id') ?: null;
        if ($branchId) {
            $request->validate([
                'hotel_branch_id' => [
                    'required',
                    'integer',
                    Rule::exists('hotel_branches', 'id')->where('hotel_id', $hotel->id),
                ],
            ]);
        }

        $input = $request->only(['template', 'show_clock', 'lock_home_scroll', 'tile_size', 'tagline', 'headline', 'items']);
        $errors = GuestLayoutConfig::validate($input);
        if ($errors) {
            $routeParams = $branchId ? ['branch_id' => $branchId] : [];

            return redirect()->route('admin.layout.edit', $routeParams)->with('layout_errors', $errors);
        }

        if ($branchId) {
            $store->saveForBranch($branchId, $input);
            $redirectParams = ['branch_id' => $branchId];
        } else {
            $store->save($hotel->id, $input);
            $redirectParams = [];
        }

        return redirect()->route('admin.layout.edit', $redirectParams)
            ->with('layout_saved', __('admin.messages.layout_saved'));
    }

    public function resetBranchLayout(Request $request, GuestLayoutStore $store): RedirectResponse
    {
        $hotel = app(CurrentHotel::class)->get();
        $this->authorize('update', $hotel);

        $validated = $request->validate([
            'hotel_branch_id' => [
                'required',
                'integer',
                Rule::exists('hotel_branches', 'id')->where('hotel_id', $hotel->id),
            ],
        ]);

        $store->resetForBranch($validated['hotel_branch_id']);

        return redirect()->route('admin.layout.edit', ['branch_id' => $validated['hotel_branch_id']])
            ->with('layout_saved', __('Branch layout reset to hotel master layout.'));
    }
}
