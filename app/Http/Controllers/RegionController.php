<?php

namespace App\Http\Controllers;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class RegionController extends Controller
{
    private const ENDPOINTS = [
        'provinces' => 'provinces.json',
        'regencies' => 'regencies/%s.json',
    ];

    public function index(string $level, ?string $parent = null): JsonResponse
    {
        abort_unless(array_key_exists($level, self::ENDPOINTS), 404);

        if ($level !== 'provinces') {
            abort_unless($parent && ctype_digit($parent), 404);
        }

        $path = sprintf(self::ENDPOINTS[$level], $parent);
        $cacheKey = "regions.{$level}.".($parent ?: 'all');

        try {
            $regions = Cache::remember($cacheKey, now()->addDays(30), function () use ($path) {
                $response = Http::acceptJson()->timeout(10)->retry(2, 250)
                    ->get(rtrim(config('services.regions.url'), '/').'/'.$path)
                    ->throw();

                $payload = $response->json();

                return isset($payload['data']) && is_array($payload['data'])
                    ? $payload['data']
                    : $payload;
            });

            return response()->json(['data' => array_values($regions ?? [])]);
        } catch (ConnectionException $exception) {
            report($exception);

            return response()->json(['message' => 'Data wilayah sedang tidak dapat diakses. Silakan coba lagi.'], 503);
        } catch (\Throwable $exception) {
            report($exception);

            return response()->json(['message' => 'Data wilayah sedang tidak dapat diakses. Silakan coba lagi.'], 503);
        }
    }
}
