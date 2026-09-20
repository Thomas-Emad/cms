<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\EventController as AdminEventController;
use App\Http\Controllers\Admin\ExperienceController as AdminExperienceController;
use App\Http\Controllers\Admin\FacilityController as AdminFacilityController;
use App\Http\Controllers\Admin\OfferController as AdminOfferController;
use App\Http\Controllers\Admin\PageController as AdminPageController;
use App\Http\Controllers\Admin\RestaurantController as AdminRestaurantController;
use App\Http\Controllers\Admin\ServiceController as AdminServiceController;
use App\Http\Controllers\Guest\EventController as GuestEventController;
use App\Http\Controllers\Guest\ExperienceController as GuestExperienceController;
use App\Http\Controllers\Guest\FacilityController as GuestFacilityController;
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
| NOT a hardcoded template. Phase 1's placeholder Guest\HomeController and
| Pages/Guest/Home.vue were dead code (never routed to) and have been
| removed.
*/

Route::middleware(['web', 'resolve.hotel'])->group(function () {
    Route::get('/', [GuestPageController::class, 'home'])->name('guest.home');

    Route::get('/facilities', [GuestFacilityController::class, 'index'])->name('guest.facilities.index');
    Route::get('/facilities/{facility:slug}', [GuestFacilityController::class, 'show'])->name('guest.facilities.show');

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

            // Page Builder
            Route::get('pages/', [AdminPageController::class, 'index'])->name('pages.index');
            Route::get('pages/create', [AdminPageController::class, 'create'])->name('pages.create');
            Route::post('pages/store', [AdminPageController::class, 'store'])->name('pages.store');
            Route::get('pages/{page}/builder', [AdminPageController::class, 'edit'])->name('pages.builder');
            Route::get('pages/{page}/preview', [AdminPageController::class, 'preview'])->name('pages.preview');
            Route::put('pages/{page}/draft', [AdminPageController::class, 'updateDraft'])->name('pages.draft.update');
            Route::post('pages/{page}/resolve-preview', [AdminPageController::class, 'resolvePreviewSection'])
                ->name('pages.resolve-preview');
            // Publishing has its own tighter policy check (hotel_admin+
            // only) enforced inside PageController::publish() itself via
            // $this->authorize('publish', $page) - not narrowed at the
            // route-middleware level since the role gate here already
            // covers hotel_staff for the OTHER page actions above.
            Route::post('pages/{page}/publish', [AdminPageController::class, 'publish'])->name('pages.publish');
        });
    });

// Minimal auth (login/logout only) - see routes/auth.php
require __DIR__ . '/auth.php';
