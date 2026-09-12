<?php

/**
 * Replaces the Page-related lines inside the admin route group in
 * routes/web.php with this complete set. Merge into the existing
 * ->prefix('admin')->name('admin.')->group() block, inside the
 * role:super_admin,hotel_admin,hotel_staff middleware group, in place of
 * the previous 4-line Page Builder block.
 */

use App\Http\Controllers\Admin\PageController as AdminPageController;
use Illuminate\Support\Facades\Route;

Route::get('pages', [AdminPageController::class, 'index'])->name('pages.index');
Route::get('pages/create', [AdminPageController::class, 'create'])->name('pages.create');
Route::post('pages', [AdminPageController::class, 'store'])->name('pages.store');
Route::get('pages/{page}/builder', [AdminPageController::class, 'edit'])->name('pages.builder');
Route::get('pages/{page}/preview', [AdminPageController::class, 'preview'])->name('pages.preview');
Route::put('pages/{page}/draft', [AdminPageController::class, 'updateDraft'])->name('pages.draft.update');
Route::post('pages/{page}/resolve-preview', [AdminPageController::class, 'resolvePreviewSection'])
    ->name('pages.resolve-preview');
// Publish keeps its own tighter check via $this->authorize('publish', $page)
// inside the controller (hotel_admin+ only) - the role middleware here
// already covers hotel_staff for every OTHER page action above.
Route::post('pages/{page}/publish', [AdminPageController::class, 'publish'])->name('pages.publish');
