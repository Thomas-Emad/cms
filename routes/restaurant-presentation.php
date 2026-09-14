<?php

/**
 * Add inside the existing admin ->prefix('admin')->name('admin.')->group()
 * block, in the role:super_admin,hotel_admin,hotel_staff middleware group,
 * alongside the existing restaurants resource routes.
 */

use App\Http\Controllers\Admin\RestaurantPresentationController;
use Illuminate\Support\Facades\Route;

Route::prefix('restaurants/{restaurant}/presentation')->name('restaurants.presentation.')->group(function () {
    Route::get('builder', [RestaurantPresentationController::class, 'edit'])->name('builder');
    Route::get('preview', [RestaurantPresentationController::class, 'preview'])->name('preview');
    Route::put('draft', [RestaurantPresentationController::class, 'updateDraft'])->name('draft.update');
    Route::post('resolve-preview', [RestaurantPresentationController::class, 'resolvePreviewSection'])->name('resolve-preview');
    Route::post('publish', [RestaurantPresentationController::class, 'publish'])->name('publish');
});
