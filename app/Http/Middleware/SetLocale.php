<?php

namespace App\Http\Middleware;

use App\Services\Tenancy\CurrentHotel;
use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public const SUPPORTED_LOCALES = ['en', 'ar'];

    public const DEFAULT_LOCALE = 'en';

    public function handle(Request $request, Closure $next): Response
    {
        $selected = null;

        // Optional convenience query parameter ?lang=
        if ($request->has('lang') && in_array($request->query('lang'), self::SUPPORTED_LOCALES, true)) {
            $selected = (string) $request->query('lang');
            session(['locale' => $selected]);
            cookie()->queue('locale', $selected, 60 * 24 * 365);
        }

        // 1. User-selected locale persisted in session/cookie
        if (! $selected) {
            $sessionLocale = session('locale');
            if ($sessionLocale && in_array($sessionLocale, self::SUPPORTED_LOCALES, true)) {
                $selected = $sessionLocale;
            } elseif ($cookieLocale = $request->cookie('locale')) {
                if (in_array($cookieLocale, self::SUPPORTED_LOCALES, true)) {
                    $selected = $cookieLocale;
                    session(['locale' => $selected]);
                }
            }
        }

        // 2. Current hotel's default_locale
        if (! $selected && app()->bound(CurrentHotel::class)) {
            try {
                $hotel = app(CurrentHotel::class)->get();
                $hotelDefault = $hotel->default_locale ?? $hotel->settings?->default_locale;
                if ($hotelDefault && in_array($hotelDefault, self::SUPPORTED_LOCALES, true)) {
                    $selected = $hotelDefault;
                }
            } catch (\Throwable) {
                // Hotel cannot be resolved or database not ready
            }
        }

        // 3. English fallback
        if (! $selected || ! in_array($selected, self::SUPPORTED_LOCALES, true)) {
            $selected = self::DEFAULT_LOCALE;
        }

        app()->setLocale($selected);
        if (class_exists(Carbon::class)) {
            Carbon::setLocale($selected);
        }

        return $next($request);
    }
}
