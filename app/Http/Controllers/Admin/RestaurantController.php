<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Restaurants\UpdateMenuAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreRestaurantRequest;
use App\Http\Requests\Admin\UpdateMenuRequest;
use App\Models\Restaurant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class RestaurantController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Restaurant::class);

        $branchId = $request->integer('branch_id') ?: null;

        return Inertia::render('Admin/Restaurants/Index', [
            'selected_branch_id' => $branchId,
            'restaurants' => Restaurant::query()
                ->when($branchId, fn ($q) => $q->where('hotel_branch_id', $branchId))
                ->ordered()
                ->with(['cover', 'branch:id,name,city'])
                ->paginate(20)
                ->withQueryString()
                ->through(fn (Restaurant $r) => [
                    ...$r->only(['id', 'name', 'slug', 'cuisine', 'status', 'featured', 'hotel_branch_id']),
                    'branch' => $r->branch ? ['id' => $r->branch->id, 'name' => $r->branch->name, 'city' => $r->branch->city] : null,
                    'cover_image_url' => $r->cover_image_url,
                ]),
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Restaurant::class);

        return Inertia::render('Admin/Restaurants/Edit', ['restaurant' => null]);
    }

    public function store(StoreRestaurantRequest $request): RedirectResponse
    {
        $restaurant = Restaurant::create($request->validated());

        return redirect()->route('admin.restaurants.edit', $restaurant)
            ->with('success', __('admin.messages.restaurant_created'));
    }

    public function edit(Restaurant $restaurant): Response
    {
        $this->authorize('update', $restaurant);

        $restaurant->load('activeMenu.categories.items');

        return Inertia::render('Admin/Restaurants/Edit', [
            'restaurant' => $restaurant,
        ]);
    }

    public function update(StoreRestaurantRequest $request, Restaurant $restaurant): RedirectResponse
    {
        $restaurant->update($request->validated());

        return redirect()->route('admin.restaurants.index')->with('success', __('admin.messages.restaurant_updated'));
    }

    public function destroy(Restaurant $restaurant): RedirectResponse
    {
        $this->authorize('delete', $restaurant);

        $restaurant->delete();

        return redirect()->route('admin.restaurants.index')->with('success', __('admin.messages.restaurant_deleted'));
    }

    public function updateMenu(UpdateMenuRequest $request, Restaurant $restaurant, UpdateMenuAction $action): RedirectResponse
    {
        $action->execute($restaurant, $request->validated('categories'));

        return redirect()->route('admin.restaurants.edit', $restaurant)->with('success', __('admin.messages.menu_saved'));
    }
}
