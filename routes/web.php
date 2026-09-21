<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\EventController as AdminEventController;
use App\Http\Controllers\Admin\ExperienceController as AdminExperienceController;
use App\Http\Controllers\Admin\FacilityController as AdminFacilityController;
use App\Http\Controllers\Admin\GalleryController as AdminGalleryController;
use App\Http\Controllers\Admin\MapController as AdminMapController;
use App\Http\Controllers\Admin\InfoEntryController;
use App\Http\Controllers\Admin\MediaController;
use App\Http\Controllers\Admin\RoomController as AdminRoomController;
use App\Http\Controllers\Admin\OfferController as AdminOfferController;
use App\Http\Controllers\Admin\PageController as AdminPageController;
use App\Http\Controllers\Admin\RestaurantController as AdminRestaurantController;
use App\Http\Controllers\Admin\ServiceController as AdminServiceController;
use App\Http\Controllers\Admin\SettingsController as AdminSettingsController;
use App\Http\Controllers\Guest\EventController as GuestEventController;
use App\Http\Controllers\Guest\ExperienceController as GuestExperienceController;
use App\Http\Controllers\Guest\FacilityController as GuestFacilityController;
use App\Http\Controllers\Guest\GalleryController as GuestGalleryController;
use App\Http\Controllers\Guest\InfoPageController;
use App\Http\Controllers\Guest\MapController as GuestMapController;
use App\Http\Controllers\Guest\RoomController as GuestRoomController;
use App\Http\Controllers\Guest\OfferController as GuestOfferController;
use App\Http\Controllers\Guest\PageController as GuestPageController;
use App\Http\Controllers\Guest\RestaurantController as GuestRestaurantController;
use App\Http\Controllers\Guest\ServiceController as GuestServiceController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Guest routes (public hotel site) - middleware: web, resolve.hotel
|--------------------------------------------------------------------------
| The homepage ('/') is a real Page Builder page resolved via is_home -
| NOT a hardcoded template. This replaced Phase 1's placeholder
| HomeController once the Page Builder landed in Phase 3.
*/

Route::middleware(['web', 'resolve.hotel'])->group(function () {
    Route::get('/', [GuestPageController::class, 'home'])->name('guest.home');

    Route::get('/facilities', [GuestFacilityController::class, 'index'])->name('guest.facilities.index');
    Route::get('/facilities/{facility:slug}', [GuestFacilityController::class, 'show'])->name('guest.facilities.show');

    // Guest-screen dock destinations
    Route::get('/meeting-rooms', [GuestFacilityController::class, 'meetingRooms'])->name('guest.meeting-rooms');
    Route::get('/rooms', [GuestRoomController::class, 'index'])->name('guest.rooms.index');
    Route::get('/rooms/{room:slug}', [GuestRoomController::class, 'show'])->name('guest.rooms.show');
    Route::get('/timing', [InfoPageController::class, 'timing'])->name('guest.timing');
    Route::get('/short-calls', [InfoPageController::class, 'shortCalls'])->name('guest.short-calls');
    Route::get('/gallery', GuestGalleryController::class)->name('guest.gallery');
    Route::get('/map', GuestMapController::class)->name('guest.map');

    Route::get('/restaurants', [GuestRestaurantController::class, 'index'])->name('guest.restaurants.index');
    Route::get('/restaurants/{restaurant:slug}', [GuestRestaurantController::class, 'show'])->name('guest.restaurants.show');

    Route::get('/services', [GuestServiceController::class, 'index'])->name('guest.services.index');
    // No guest.services.show by design - services are a single list page, no detail route.

    Route::get('/events', [GuestEventController::class, 'index'])->name('guest.events.index');
    Route::get('/events/{event:slug}', [GuestEventController::class, 'show'])->name('guest.events.show');

    Route::get('/offers', [GuestOfferController::class, 'index'])->name('guest.offers.index');
    Route::get('/offers/{offer:slug}', [GuestOfferController::class, 'show'])->name('guest.offers.show');

    Route::get('/experiences', [GuestExperienceController::class, 'index'])->name('guest.experiences.index');
    Route::get('/experiences/{experience:slug}', [GuestExperienceController::class, 'show'])->name('guest.experiences.show');

    // Page Builder pages other than the homepage. Deliberately under
    // /pages/{slug} rather than a bare /{slug} catch-all, to avoid
    // colliding with the static routes above (e.g. a page slugged
    // "facilities" would otherwise be unreachable or would swallow the
    // real Facilities index). This is an open routing decision - see
    // ROUTING_DECISION_NEEDED.md - not yet resolved by design.
    Route::get('/pages/{slug}', [GuestPageController::class, 'show'])->name('guest.pages.show');
});

/*
|--------------------------------------------------------------------------
| Admin routes - middleware: web, auth, resolve.hotel, role:...
|--------------------------------------------------------------------------
*/
Route::middleware(['web', 'auth', 'resolve.hotel'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/dashboard', DashboardController::class)
            ->middleware('role:super_admin,hotel_admin,hotel_staff')
            ->name('dashboard');

        Route::middleware('role:super_admin,hotel_admin,hotel_staff')->group(function () {
            Route::resource('facilities', AdminFacilityController::class)->except(['show']);

            Route::resource('restaurants', AdminRestaurantController::class)->except(['show']);
            Route::put('restaurants/{restaurant}/menu', [AdminRestaurantController::class, 'updateMenu'])
                ->name('restaurants.menu.update');

            Route::resource('services', AdminServiceController::class)->except(['show']);
            Route::resource('events', AdminEventController::class)->except(['show']);
            Route::resource('offers', AdminOfferController::class)->except(['show']);
            Route::resource('experiences', AdminExperienceController::class)->except(['show']);

            // Guest-screen content
            Route::resource('rooms', AdminRoomController::class)->except(['show']);

            Route::get('timing', [InfoEntryController::class, 'edit'])->defaults('kind', 'timing')->name('timing.edit');
            Route::put('timing', [InfoEntryController::class, 'update'])->defaults('kind', 'timing')->name('timing.update');
            Route::get('short-calls', [InfoEntryController::class, 'edit'])->defaults('kind', 'short_call')->name('short-calls.edit');
            Route::put('short-calls', [InfoEntryController::class, 'update'])->defaults('kind', 'short_call')->name('short-calls.update');

            Route::get('gallery', [AdminGalleryController::class, 'index'])->name('gallery.index');

            Route::get('map', [AdminMapController::class, 'edit'])->name('map.edit');
            Route::get('map/builder', [AdminMapController::class, 'builder'])->name('map.builder');
            Route::put('map/save', [AdminMapController::class, 'save'])->name('map.save');
            Route::put('map', [AdminMapController::class, 'update'])->name('map.update');
            Route::post('map/demo', [AdminMapController::class, 'demo'])->name('map.demo');

            // Image upload / delete / reorder for any HasMedia owner
            Route::post('media', [MediaController::class, 'store'])->name('media.store');
            Route::put('media/reorder', [MediaController::class, 'reorder'])->name('media.reorder');
            Route::delete('media/{media}', [MediaController::class, 'destroy'])->name('media.destroy');

            // Page Builder
            Route::get('pages/', [AdminPageController::class, 'index'])->name('pages.index');
            Route::get('pages/create', [AdminPageController::class, 'create'])->name('pages.create');
            Route::post('pages/store', [AdminPageController::class, 'store'])->name('pages.store');
            Route::get('pages/{page}/builder', [AdminPageController::class, 'edit'])->name('pages.builder');
            Route::get('pages/{page}/preview', [AdminPageController::class, 'preview'])->name('pages.preview');
            Route::put('pages/{page}/draft', [AdminPageController::class, 'updateDraft'])->name('pages.draft.update');
            Route::patch('pages/{page}/layout', [AdminPageController::class, 'updateLayout'])->name('pages.layout.update');
            Route::post('pages/{page}/resolve-preview', [AdminPageController::class, 'resolvePreviewSection'])
                ->name('pages.resolve-preview');
            // Publishing has its own tighter policy check (hotel_admin+
            // only) enforced inside PageController::publish() itself via
            // $this->authorize('publish', $page) - not narrowed at the
            // route-middleware level since the role gate here already
            // covers hotel_staff for the OTHER page actions above.
            Route::post('pages/{page}/publish', [AdminPageController::class, 'publish'])->name('pages.publish');

            Route::get('settings', [AdminSettingsController::class, 'index'])->name('settings.index');
            Route::patch('settings/guest-view', [AdminSettingsController::class, 'updateGuestView'])->name('settings.guest-view.update');
        });
    });

// Minimal auth (login/logout only) - see routes/auth.php
require __DIR__ . '/auth.php';
