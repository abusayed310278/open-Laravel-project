<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LocationController extends Controller
{
    /**
     * Get Google Maps API key from config.
     */
    protected function getApiKey(): string
    {
        return (string) config('services.google.maps_api_key', 'AIzaSyCsdLSxCzJS1DypOOyGan4BWTZvZIhiS9M');
    }

    /**
     * Get current / detected live location via Google Maps Geocoding API.
     */
    public function current(Request $request): JsonResponse
    {
        $lat = $request->input('latitude') ?? $request->input('lat');
        $lng = $request->input('longitude') ?? $request->input('lng');

        // If coordinates provided, reverse geocode directly
        if ($lat !== null && $lng !== null && is_numeric($lat) && is_numeric($lng)) {
            $parsed = $this->reverseGeocodeCoordinates((float) $lat, (float) $lng);
            if ($parsed) {
                return response()->json([
                    'status' => 'success',
                    'data' => $parsed,
                ]);
            }
        }

        // If user is authenticated, check if they have a saved default address
        if ($request->user()) {
            $defaultAddress = $request->user()->addresses()->where('is_default', true)->first()
                ?? $request->user()->addresses()->first();

            if ($defaultAddress) {
                $label = $defaultAddress->city ? "{$defaultAddress->city}, {$defaultAddress->country}" : ($defaultAddress->line1 ?? 'Doha, Qatar');
                return response()->json([
                    'status' => 'success',
                    'data' => [
                        'label' => $label,
                        'city' => $defaultAddress->city ?? 'Doha',
                        'country' => $defaultAddress->country ?? 'Qatar',
                        'district' => $defaultAddress->state ?? '',
                        'formatted_address' => trim("{$defaultAddress->line1}, {$defaultAddress->city}, {$defaultAddress->country}", ', '),
                        'latitude' => 25.2854,
                        'longitude' => 51.5310,
                        'source' => 'saved_address',
                    ],
                ]);
            }
        }

        // Default to Doha, Qatar coordinates and geocode via Google Maps API
        $defaultLat = 25.2854;
        $defaultLng = 51.5310;
        $parsed = $this->reverseGeocodeCoordinates($defaultLat, $defaultLng);

        return response()->json([
            'status' => 'success',
            'data' => $parsed ?? [
                'label' => 'Doha, Qatar',
                'city' => 'Doha',
                'country' => 'Qatar',
                'district' => 'Doha',
                'formatted_address' => 'Doha, Qatar',
                'latitude' => $defaultLat,
                'longitude' => $defaultLng,
                'source' => 'default',
            ],
        ]);
    }

    /**
     * Reverse geocode given coordinates using Google Maps Geocoding API.
     */
    public function reverseGeocode(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
        ]);

        $lat = (float) $validated['latitude'];
        $lng = (float) $validated['longitude'];

        $parsed = $this->reverseGeocodeCoordinates($lat, $lng);

        if (! $parsed) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unable to resolve location from Google Maps API.',
            ], 422);
        }

        return response()->json([
            'status' => 'success',
            'data' => $parsed,
        ]);
    }

    /**
     * Search places / addresses via Google Maps Geocoding API.
     */
    public function search(Request $request): JsonResponse
    {
        $query = trim((string) $request->input('query', ''));
        if (empty($query)) {
            return response()->json([
                'status' => 'success',
                'data' => $this->getPopularZones(),
            ]);
        }

        $cacheKey = 'gmaps_search_' . md5(strtolower($query));
        $results = Cache::remember($cacheKey, 3600, function () use ($query) {
            try {
                $response = Http::timeout(8)->get('https://maps.googleapis.com/maps/api/geocode/json', [
                    'address' => $query,
                    'key' => $this->getApiKey(),
                ]);

                if (! $response->successful()) {
                    return [];
                }

                $json = $response->json();
                if (($json['status'] ?? '') !== 'OK' || empty($json['results'])) {
                    return [];
                }

                $items = [];
                foreach (array_slice($json['results'], 0, 8) as $result) {
                    $parsed = $this->parseGeocodeResult($result);
                    if ($parsed) {
                        $items[] = $parsed;
                    }
                }

                return $items;
            } catch (\Throwable $e) {
                Log::warning('Google Maps Search API error: ' . $e->getMessage());
                return [];
            }
        });

        return response()->json([
            'status' => 'success',
            'data' => $results,
        ]);
    }

    /**
     * Popular delivery areas in Qatar with preset coordinates.
     */
    public function popularZones(): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'data' => $this->getPopularZones(),
        ]);
    }

    /**
     * Helper to call Google Maps Reverse Geocoding API with caching.
     */
    protected function reverseGeocodeCoordinates(float $lat, float $lng): ?array
    {
        $roundedLat = round($lat, 4);
        $roundedLng = round($lng, 4);
        $cacheKey = "gmaps_rev_geo_{$roundedLat}_{$roundedLng}";

        return Cache::remember($cacheKey, 86400, function () use ($lat, $lng) {
            try {
                $response = Http::timeout(8)->get('https://maps.googleapis.com/maps/api/geocode/json', [
                    'latlng' => "{$lat},{$lng}",
                    'key' => $this->getApiKey(),
                ]);

                if (! $response->successful()) {
                    Log::warning('Google Maps API HTTP error: ' . $response->status());
                    return null;
                }

                $json = $response->json();
                if (($json['status'] ?? '') !== 'OK' || empty($json['results'])) {
                    Log::warning('Google Maps API Status: ' . ($json['status'] ?? 'unknown'));
                    return null;
                }

                return $this->parseGeocodeResult($json['results'][0], $lat, $lng);
            } catch (\Throwable $e) {
                Log::error('Google Maps API Reverse Geocoding exception: ' . $e->getMessage());
                return null;
            }
        });
    }

    /**
     * Parse Google Maps Geocoding API result into clean location payload.
     */
    protected function parseGeocodeResult(array $result, ?float $fallbackLat = null, ?float $fallbackLng = null): ?array
    {
        $components = $result['address_components'] ?? [];
        $city = '';
        $country = '';
        $district = '';
        $state = '';
        $street = '';

        foreach ($components as $c) {
            $types = $c['types'] ?? [];
            if (in_array('locality', $types) || in_array('postal_town', $types)) {
                $city = $c['long_name'];
            } elseif (empty($city) && (in_array('administrative_area_level_2', $types) || in_array('sublocality', $types))) {
                $city = $c['long_name'];
            }

            if (in_array('country', $types)) {
                $country = $c['long_name'];
            }

            if (in_array('sublocality_level_1', $types) || in_array('neighborhood', $types) || in_array('administrative_area_level_3', $types)) {
                $district = $c['long_name'];
            }

            if (in_array('administrative_area_level_1', $types)) {
                $state = $c['long_name'];
            }

            if (in_array('route', $types)) {
                $street = $c['long_name'];
            }
        }

        if (empty($city)) {
            $city = $state ?: 'Doha';
        }
        if (empty($country)) {
            $country = 'Qatar';
        }

        $label = $district && $city && $district !== $city
            ? "{$district}, {$city}"
            : "{$city}, {$country}";

        $lat = $result['geometry']['location']['lat'] ?? $fallbackLat ?? 25.2854;
        $lng = $result['geometry']['location']['lng'] ?? $fallbackLng ?? 51.5310;

        return [
            'label' => $label,
            'city' => $city,
            'country' => $country,
            'district' => $district,
            'street' => $street,
            'formatted_address' => $result['formatted_address'] ?? "{$city}, {$country}",
            'place_id' => $result['place_id'] ?? '',
            'latitude' => (float) $lat,
            'longitude' => (float) $lng,
            'source' => 'google_maps',
        ];
    }

    /**
     * Preset prominent Qatar delivery zones.
     */
    protected function getPopularZones(): array
    {
        return [
            [
                'label' => 'Doha, Qatar',
                'city' => 'Doha',
                'country' => 'Qatar',
                'district' => 'Central Doha',
                'formatted_address' => 'Doha, Qatar',
                'latitude' => 25.2854,
                'longitude' => 51.5310,
            ],
            [
                'label' => 'The Pearl, Doha',
                'city' => 'Doha',
                'country' => 'Qatar',
                'district' => 'The Pearl-Qatar',
                'formatted_address' => 'The Pearl, Doha, Qatar',
                'latitude' => 25.3713,
                'longitude' => 51.5513,
            ],
            [
                'label' => 'Lusail City, Qatar',
                'city' => 'Lusail',
                'country' => 'Qatar',
                'district' => 'Marina District',
                'formatted_address' => 'Lusail City, Qatar',
                'latitude' => 25.4206,
                'longitude' => 51.4912,
            ],
            [
                'label' => 'West Bay, Doha',
                'city' => 'Doha',
                'country' => 'Qatar',
                'district' => 'West Bay',
                'formatted_address' => 'West Bay, Diplomatic Area, Doha, Qatar',
                'latitude' => 25.3217,
                'longitude' => 51.5323,
            ],
            [
                'label' => 'Al Rayyan, Qatar',
                'city' => 'Al Rayyan',
                'country' => 'Qatar',
                'district' => 'Education City',
                'formatted_address' => 'Al Rayyan, Qatar',
                'latitude' => 25.3286,
                'longitude' => 51.4244,
            ],
            [
                'label' => 'Al Wakrah, Qatar',
                'city' => 'Al Wakrah',
                'country' => 'Qatar',
                'district' => 'Old Souq',
                'formatted_address' => 'Al Wakrah, Qatar',
                'latitude' => 25.1768,
                'longitude' => 51.6034,
            ],
        ];
    }
}
