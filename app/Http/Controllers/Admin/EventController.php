<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreEventRequest;
use App\Models\Event;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class EventController extends Controller
{
    public function index(): Response
    {
        $this->authorize('viewAny', Event::class);

        return Inertia::render('Admin/Events/Index', [
            'events' => Event::query()->orderBy('start_date')->with('cover')->paginate(20)
                ->through(fn (Event $e) => [
                    ...$e->only(['id', 'title', 'slug', 'start_date', 'status']),
                    'cover_image_url' => $e->cover_image_url,
                ]),
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Event::class);

        return Inertia::render('Admin/Events/Edit', ['event' => null]);
    }

    public function store(StoreEventRequest $request): RedirectResponse
    {
        Event::create($request->validated());

        return redirect()->route('admin.events.index')->with('success', __('admin.messages.event_created'));
    }

    public function edit(Event $event): Response
    {
        $this->authorize('update', $event);

        return Inertia::render('Admin/Events/Edit', ['event' => $event]);
    }

    public function update(StoreEventRequest $request, Event $event): RedirectResponse
    {
        $event->update($request->validated());

        return redirect()->route('admin.events.index')->with('success', __('admin.messages.event_updated'));
    }

    public function destroy(Event $event): RedirectResponse
    {
        $this->authorize('delete', $event);

        $event->delete();

        return redirect()->route('admin.events.index')->with('success', __('admin.messages.event_deleted'));
    }
}
