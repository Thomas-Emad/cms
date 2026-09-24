<?php

namespace App\Http\Controllers;

use App\Http\Middleware\SetLocale;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LocaleController extends Controller
{
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'locale' => ['required', 'string', 'in:'.implode(',', SetLocale::SUPPORTED_LOCALES)],
        ]);

        $locale = $validated['locale'];
        session(['locale' => $locale]);
        cookie()->queue('locale', $locale, 60 * 24 * 365);

        return back();
    }
}
