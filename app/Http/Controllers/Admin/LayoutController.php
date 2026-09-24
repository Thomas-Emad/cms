<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Layout\GuestLayoutConfig;
use App\Services\Layout\GuestLayoutStore;
use App\Services\Tenancy\CurrentHotel;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/** Admin: choose the guest-screen layout template and edit its menu. */
class LayoutController extends Controller
{
    public function edit(GuestLayoutStore $store): Response
    {
        $hotel = app(CurrentHotel::class)->get();
        $this->authorize('view', $hotel);

        return Inertia::render('Admin/Layout/Edit', [
            'config' => $store->forHotel($hotel->id),
            'defaults' => GuestLayoutConfig::defaults(),
            'errors_list' => session()->pull('layout_errors', []),
            'flash_ok' => session()->pull('layout_saved'),
        ]);
    }

    public function update(Request $request, GuestLayoutStore $store): RedirectResponse
    {
        $hotel = app(CurrentHotel::class)->get();
        $this->authorize('update', $hotel);

        $input = $request->only(['template', 'show_clock', 'lock_home_scroll', 'tile_size', 'tagline', 'headline', 'items']);
        $errors = GuestLayoutConfig::validate($input);
        if ($errors) {
            return redirect()->route('admin.layout.edit')->with('layout_errors', $errors);
        }

        $store->save($hotel->id, $input);

        return redirect()->route('admin.layout.edit')->with('layout_saved', __('admin.messages.layout_saved'));
    }
}
