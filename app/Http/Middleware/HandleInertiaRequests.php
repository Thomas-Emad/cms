<?php

namespace App\Http\Middleware;

use App\Services\Layout\GuestLayoutStore;
use App\Services\Tenancy\CurrentHotel;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function share(Request $request): array
    {
        return array_merge(parent::share($request), [
            'auth' => [
                'user' => $request->user()
                    ? $request->user()->only(['id', 'name', 'email', 'role', 'hotel_id'])
                    : null,
            ],
            'hotel' => fn () => app(CurrentHotel::class)->has()
                ? app(CurrentHotel::class)->get()->only(['id', 'name', 'slug', 'status'])
                : null,
            // Which guest-screen template + menu to use. Not needed (so not queried) in the admin area.
            'guestLayout' => fn () => $request->is('admin*') || ! app(CurrentHotel::class)->has()
                ? null
                : app(GuestLayoutStore::class)->forHotel(app(CurrentHotel::class)->get()->id),
            'locale' => fn () => app()->getLocale(),
            'direction' => fn () => app()->getLocale() === 'ar' ? 'rtl' : 'ltr',
            'translations' => fn () => $this->translations(),
        ]);
    }

    protected function translations(): array
    {
        $locale = app()->getLocale();
        $translations = [];

        // 1. Load PHP translation files from lang/{locale}/*.php
        $phpPath = base_path("lang/{$locale}");
        if (is_dir($phpPath)) {
            $files = glob("{$phpPath}/*.php") ?: [];
            foreach ($files as $file) {
                $group = basename($file, '.php');
                $translations[$group] = require $file;
            }
        }

        // 2. Load JSON translations from lang/{locale}.json
        $path = base_path("lang/{$locale}.json");
        if (file_exists($path)) {
            $decoded = json_decode((string) file_get_contents($path), true);
            if (is_array($decoded)) {
                $translations = array_replace_recursive($translations, $decoded);
            }
        }

        return $translations;
    }
}
