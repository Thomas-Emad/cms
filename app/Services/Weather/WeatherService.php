<?php

namespace App\Services\Weather;

use App\Models\Hotel;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WeatherService
{
    /**
     * Retrieve the last $days of historical weather observations for a hotel and specific branch.
     *
     * @return array{
     *     status: 'ok'|'error',
     *     location: array{city: string, branch_name: string, latitude: float, longitude: float, timezone: string},
     *     branches: array<int, array{id: string, name: string, city: string, is_default: bool}>,
     *     current_branch_id: string,
     *     units: array{temperature: string, precipitation: string, wind: string},
     *     days: array<int, array{
     *         date: string,
     *         day_name: string,
     *         day_number: string,
     *         month_name: string,
     *         weather_code: int,
     *         condition_key: string,
     *         icon: string,
     *         temp_max: float,
     *         temp_min: float,
     *         apparent_temp_max: float,
     *         apparent_temp_min: float,
     *         precipitation: float,
     *         wind_speed: float
     *     }>,
     *     error?: string
     * }
     */
    public function getPastDaysWeather(Hotel $hotel, ?string $branchId = null, int $days = 5): array
    {
        $branches = $this->getBranches($hotel);
        $currentBranch = $this->resolveCurrentBranch($branches, $branchId);
        $locale = app()->getLocale();

        $formattedBranches = array_map(function ($b) use ($locale) {
            return [
                'id' => $b['id'],
                'name' => is_array($b['name']) ? ($b['name'][$locale] ?? $b['name']['en'] ?? reset($b['name'])) : (string) $b['name'],
                'city' => is_array($b['city']) ? ($b['city'][$locale] ?? $b['city']['en'] ?? reset($b['city'])) : (string) $b['city'],
                'is_default' => (bool) ($b['is_default'] ?? false),
            ];
        }, $branches);

        $location = [
            'city' => is_array($currentBranch['city']) ? ($currentBranch['city'][$locale] ?? $currentBranch['city']['en'] ?? reset($currentBranch['city'])) : (string) $currentBranch['city'],
            'branch_name' => is_array($currentBranch['name']) ? ($currentBranch['name'][$locale] ?? $currentBranch['name']['en'] ?? reset($currentBranch['name'])) : (string) $currentBranch['name'],
            'latitude' => (float) $currentBranch['latitude'],
            'longitude' => (float) $currentBranch['longitude'],
            'timezone' => (string) ($currentBranch['timezone'] ?? ($hotel->timezone ?: 'auto')),
        ];

        $cacheTtl = (int) config('services.weather.cache_ttl', 3600);
        $cacheKey = "hotel:{$hotel->id}:weather:branch:{$currentBranch['id']}:past{$days}:v1";

        try {
            $weatherData = Cache::remember($cacheKey, $cacheTtl, function () use ($location, $days) {
                return $this->fetchFromOpenMeteo($location, $days);
            });

            return array_merge($weatherData, [
                'branches' => $formattedBranches,
                'current_branch_id' => $currentBranch['id'],
            ]);
        } catch (Exception $e) {
            Log::warning('WeatherService fetch failed: '.$e->getMessage(), [
                'hotel_id' => $hotel->id,
                'branch_id' => $currentBranch['id'],
                'location' => $location,
            ]);

            return [
                'status' => 'error',
                'location' => $location,
                'branches' => $formattedBranches,
                'current_branch_id' => $currentBranch['id'],
                'units' => [
                    'temperature' => '°C',
                    'precipitation' => 'mm',
                    'wind' => 'km/h',
                ],
                'days' => [],
                'error' => 'weather.error_message',
            ];
        }
    }

    /**
     * Get all available hotel branches with their geographical coordinates.
     *
     * @return array<int, array{
     *     id: string,
     *     name: string|array<string, string>,
     *     city: string|array<string, string>,
     *     latitude: float,
     *     longitude: float,
     *     timezone?: string,
     *     is_default?: bool
     * }>
     */
    public function getBranches(Hotel $hotel): array
    {
        $meta = is_array($hotel->settings?->metadata) ? $hotel->settings->metadata : [];
        $metaWeather = is_array($meta['weather'] ?? null) ? $meta['weather'] : [];

        if (! empty($metaWeather['branches']) && is_array($metaWeather['branches'])) {
            return $metaWeather['branches'];
        }

        return $this->getDefaultBranches($hotel);
    }

    /**
     * Provide sensible localized default branches for the hotel if none are configured in settings.
     *
     * @return array<int, array{
     *     id: string,
     *     name: array<string, string>,
     *     city: array<string, string>,
     *     latitude: float,
     *     longitude: float,
     *     timezone: string,
     *     is_default: bool
     * }>
     */
    public function getDefaultBranches(Hotel $hotel): array
    {
        $meta = is_array($hotel->settings?->metadata) ? $hotel->settings->metadata : [];
        $metaWeather = is_array($meta['weather'] ?? null) ? $meta['weather'] : [];

        $mainCity = $metaWeather['city']
            ?? (is_string($hotel->address) && trim($hotel->address) !== '' ? $hotel->address : null)
            ?? config('services.weather.city', 'Horizon Bay');

        $mainLat = isset($metaWeather['latitude']) && is_numeric($metaWeather['latitude'])
            ? (float) $metaWeather['latitude']
            : (float) config('services.weather.latitude', 25.2048);

        $mainLng = isset($metaWeather['longitude']) && is_numeric($metaWeather['longitude'])
            ? (float) $metaWeather['longitude']
            : (float) config('services.weather.longitude', 55.2708);

        $tz = $hotel->timezone && $hotel->timezone !== 'UTC' ? $hotel->timezone : 'auto';

        return [
            [
                'id' => 'horizon-bay',
                'name' => [
                    'en' => 'Horizon Bay (Beach Resort)',
                    'ar' => 'هورايزون باي (المنتجع الشاطئي)',
                ],
                'city' => [
                    'en' => (string) $mainCity,
                    'ar' => 'هورايزون باي',
                ],
                'latitude' => $mainLat,
                'longitude' => $mainLng,
                'timezone' => $tz,
                'is_default' => true,
            ],
            [
                'id' => 'downtown-city',
                'name' => [
                    'en' => 'Downtown Skyline Branch',
                    'ar' => 'فرع وسط المدينة (داون تاون)',
                ],
                'city' => [
                    'en' => 'Downtown City Center',
                    'ar' => 'وسط المدينة',
                ],
                'latitude' => 25.1972,
                'longitude' => 55.2744,
                'timezone' => $tz,
                'is_default' => false,
            ],
            [
                'id' => 'palm-island',
                'name' => [
                    'en' => 'Palm Island Retreat',
                    'ar' => 'فرع ملاذ جزيرة النخلة',
                ],
                'city' => [
                    'en' => 'Palm Island Coast',
                    'ar' => 'ساحل جزيرة النخلة',
                ],
                'latitude' => 25.1124,
                'longitude' => 55.1390,
                'timezone' => $tz,
                'is_default' => false,
            ],
        ];
    }

    /**
     * Resolve the active branch from ID, falling back to default branch.
     *
     * @param  array<int, array<string, mixed>>  $branches
     * @return array<string, mixed>
     */
    public function resolveCurrentBranch(array $branches, ?string $branchId): array
    {
        if ($branchId) {
            foreach ($branches as $branch) {
                if (($branch['id'] ?? '') === $branchId) {
                    return $branch;
                }
            }
        }

        foreach ($branches as $branch) {
            if (! empty($branch['is_default'])) {
                return $branch;
            }
        }

        return $branches[0];
    }

    /**
     * Query Open-Meteo's forecast API for past days historical data.
     *
     * @param  array{city: string, branch_name?: string, latitude: float, longitude: float, timezone: string}  $location
     * @return array{
     *     status: 'ok',
     *     location: array{city: string, branch_name?: string, latitude: float, longitude: float, timezone: string},
     *     units: array{temperature: string, precipitation: string, wind: string},
     *     days: array<int, array<string, mixed>>
     * }
     */
    protected function fetchFromOpenMeteo(array $location, int $days): array
    {
        $response = Http::timeout(6)
            ->retry(1, 200)
            ->withUserAgent('HotelCMS-WeatherClient/1.0')
            ->get('https://api.open-meteo.com/v1/forecast', [
                'latitude' => $location['latitude'],
                'longitude' => $location['longitude'],
                'past_days' => $days,
                'forecast_days' => 0,
                'daily' => implode(',', [
                    'weather_code',
                    'temperature_2m_max',
                    'temperature_2m_min',
                    'apparent_temperature_max',
                    'apparent_temperature_min',
                    'precipitation_sum',
                    'wind_speed_10m_max',
                ]),
                'timezone' => $location['timezone'],
            ]);

        if (! $response->successful()) {
            throw new Exception('Open-Meteo API returned HTTP '.$response->status());
        }

        $json = $response->json();
        $daily = $json['daily'] ?? null;

        if (! is_array($daily) || ! isset($daily['time']) || ! is_array($daily['time'])) {
            throw new Exception('Invalid weather payload format from Open-Meteo');
        }

        $times = $daily['time'];
        $count = count($times);
        $resultDays = [];

        // Return the last $days entries
        $startIndex = max(0, $count - $days);

        for ($i = $startIndex; $i < $count; $i++) {
            $dateStr = $times[$i];
            $carbon = Carbon::parse($dateStr);
            $wmoCode = (int) ($daily['weather_code'][$i] ?? 0);

            $resultDays[] = [
                'date' => $dateStr,
                'day_name' => $carbon->format('l'),
                'day_number' => $carbon->format('j'),
                'month_name' => $carbon->format('M'),
                'weather_code' => $wmoCode,
                'condition_key' => $this->resolveConditionKey($wmoCode),
                'icon' => $this->resolveIcon($wmoCode),
                'temp_max' => round((float) ($daily['temperature_2m_max'][$i] ?? 0), 1),
                'temp_min' => round((float) ($daily['temperature_2m_min'][$i] ?? 0), 1),
                'apparent_temp_max' => round((float) ($daily['apparent_temperature_max'][$i] ?? 0), 1),
                'apparent_temp_min' => round((float) ($daily['apparent_temperature_min'][$i] ?? 0), 1),
                'precipitation' => round((float) ($daily['precipitation_sum'][$i] ?? 0), 1),
                'wind_speed' => round((float) ($daily['wind_speed_10m_max'][$i] ?? 0), 1),
            ];
        }

        return [
            'status' => 'ok',
            'location' => $location,
            'units' => [
                'temperature' => '°C',
                'precipitation' => 'mm',
                'wind' => 'km/h',
            ],
            'days' => $resultDays,
        ];
    }

    /**
     * Map WMO weather code to standard condition translation key.
     */
    public function resolveConditionKey(int $wmoCode): string
    {
        return match ($wmoCode) {
            0 => 'clear_sky',
            1 => 'mainly_clear',
            2 => 'partly_cloudy',
            3 => 'overcast',
            45, 48 => 'foggy',
            51, 53, 55 => 'drizzle',
            61, 63, 65 => 'rain',
            71, 73, 75, 77 => 'snow',
            80, 81, 82 => 'rain_showers',
            85, 86 => 'snow_showers',
            95, 96, 99 => 'thunderstorm',
            default => 'partly_cloudy',
        };
    }

    /**
     * Map WMO weather code to icon name for UI rendering.
     */
    public function resolveIcon(int $wmoCode): string
    {
        return match ($wmoCode) {
            0 => 'sun',
            1 => 'sun-cloud',
            2 => 'cloud-sun',
            3 => 'cloud',
            45, 48 => 'fog',
            51, 53, 55 => 'drizzle',
            61, 63, 65 => 'rain',
            71, 73, 75, 77 => 'snow',
            80, 81, 82 => 'showers',
            85, 86 => 'snow-showers',
            95, 96, 99 => 'thunderstorm',
            default => 'cloud-sun',
        };
    }
}
