<?php

namespace App\Http\Middleware;

use App\Models\Theme;
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
            'branch' => fn () => app(CurrentHotel::class)->hasBranch()
                ? app(CurrentHotel::class)->branch()->only(['id', 'name', 'slug', 'domain', 'city'])
                : null,
            'branches' => fn () => app(CurrentHotel::class)->has()
                ? app(CurrentHotel::class)->get()->branches()->ordered()->get(['id', 'name', 'slug', 'city'])
                : [],
            // Which guest-screen template + menu to use. Not needed (so not queried) in the admin area.
            'guestLayout' => fn () => $request->is('admin*') || ! app(CurrentHotel::class)->has()
                ? null
                : app(GuestLayoutStore::class)->forHotel(
                    app(CurrentHotel::class)->get()->id,
                    app(CurrentHotel::class)->branch()?->id
                ),
            'theme' => function () {
                if (! app(CurrentHotel::class)->has()) {
                    return null;
                }
                $hotel = app(CurrentHotel::class)->get();
                $currentBranch = app(CurrentHotel::class)->branch();

                $theme = null;
                if ($currentBranch) {
                    $theme = Theme::where('hotel_id', $hotel->id)
                        ->where('hotel_branch_id', $currentBranch->id)
                        ->where('is_active', true)
                        ->first();
                }

                if (! $theme) {
                    $theme = Theme::where('hotel_id', $hotel->id)
                        ->whereNull('hotel_branch_id')
                        ->where('is_active', true)
                        ->first() ?? $hotel->activeTheme ?? $hotel->themes()->first();
                }

                if ($theme) {
                    return array_merge(
                        $theme->only([
                            'id', 'name', 'primary_color', 'secondary_color',
                            'font_family', 'border_radius', 'button_style', 'card_style',
                        ]),
                        [
                            'header_bg' => $theme->header_bg,
                            'footer_bg' => $theme->footer_bg,
                            'css_variables' => $theme->toCssVariables(),
                        ]
                    );
                }

                return [
                    'primary_color' => '#059669',
                    'secondary_color' => '#10B981',
                    'header_bg' => '#064e3b',
                    'footer_bg' => '#022c22',
                    'font_family' => 'Instrument Sans',
                    'border_radius' => 'medium',
                    'button_style' => 'rounded',
                    'card_style' => 'elevated',
                    'css_variables' => [
                        '--color-primary' => '#059669',
                        '--color-secondary' => '#10B981',
                        '--header-bg' => 'rgba(6, 78, 59, 0.88)',
                        '--header-bg-solid' => '#064e3b',
                        '--footer-bg' => '#022c22',
                        '--dock-bg' => 'rgba(2, 44, 34, 0.92)',
                        '--font-family' => 'Instrument Sans',
                        '--radius' => '8px',
                    ],
                ];
            },
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
