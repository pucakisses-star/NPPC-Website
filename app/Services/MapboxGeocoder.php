<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Thin wrapper around the Mapbox v5 geocoding API.
 *
 * Returns [lat, lng] for an address string, or null if the address
 * can't be resolved or the Mapbox token isn't configured. Callers
 * should treat null as "skip" — never an error to surface to the user.
 */
final class MapboxGeocoder
{
    private const ENDPOINT = 'https://api.mapbox.com/geocoding/v5/mapbox.places/';

    /**
     * @return array{0: float, 1: float}|null  [lat, lng] or null
     */
    public function geocode(string $address): ?array
    {
        $address = trim($address);
        if ($address === '') return null;

        $token = (string) config('services.mapbox.token', '');
        if ($token === '') return null;

        $url = self::ENDPOINT . rawurlencode($address) . '.json';

        try {
            $resp = Http::timeout(8)->get($url, [
                'access_token' => $token,
                'limit'        => 1,
                'country'      => 'us',
                'language'     => 'en',
            ]);
        } catch (\Throwable $e) {
            Log::warning('Mapbox geocode HTTP error', ['address' => $address, 'message' => $e->getMessage()]);
            return null;
        }

        if (! $resp->successful()) {
            Log::warning('Mapbox geocode non-200', ['address' => $address, 'status' => $resp->status()]);
            return null;
        }

        $center = $resp->json('features.0.center');
        if (! is_array($center) || count($center) !== 2) return null;

        // Mapbox returns [lng, lat]; normalize to [lat, lng].
        [$lng, $lat] = $center;
        if (! is_numeric($lat) || ! is_numeric($lng)) return null;

        return [(float) $lat, (float) $lng];
    }
}
