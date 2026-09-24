<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use App\Services\Tenancy\CurrentHotel;
use App\Services\Weather\WeatherService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class WeatherController extends Controller
{
    /**
     * Dedicated guest weather view displaying historical observations for the last 5 days
     * with multi-branch selection support.
     */
    public function index(Request $request, CurrentHotel $currentHotel, WeatherService $weatherService): Response
    {
        $hotel = $currentHotel->get();
        $branchId = $request->query('branch');
        $weatherData = $weatherService->getPastDaysWeather($hotel, is_string($branchId) ? $branchId : null, 5);

        return Inertia::render('Guest/Weather/Index', [
            'weather' => $weatherData,
            'title' => __('weather.title'),
            'subtitle' => __('weather.subtitle'),
        ]);
    }
}
