<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreServiceRequest;
use App\Models\Service;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ServiceController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Service::class);

        $branchId = $request->integer('branch_id') ?: null;

        return Inertia::render('Admin/Services/Index', [
            'selected_branch_id' => $branchId,
            'services' => Service::query()
                ->when($branchId, fn ($q) => $q->where('hotel_branch_id', $branchId))
                ->ordered()
                ->with('branch:id,name,city')
                ->paginate(20)
                ->withQueryString(),
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Service::class);

        return Inertia::render('Admin/Services/Edit', ['service' => null]);
    }

    public function store(StoreServiceRequest $request): RedirectResponse
    {
        Service::create($request->validated());

        return redirect()->route('admin.services.index')->with('success', __('admin.messages.service_created'));
    }

    public function edit(Service $service): Response
    {
        $this->authorize('update', $service);

        return Inertia::render('Admin/Services/Edit', ['service' => $service]);
    }

    public function update(StoreServiceRequest $request, Service $service): RedirectResponse
    {
        $service->update($request->validated());

        return redirect()->route('admin.services.index')->with('success', __('admin.messages.service_updated'));
    }

    public function destroy(Service $service): RedirectResponse
    {
        $this->authorize('delete', $service);

        $service->delete();

        return redirect()->route('admin.services.index')->with('success', __('admin.messages.service_deleted'));
    }
}
