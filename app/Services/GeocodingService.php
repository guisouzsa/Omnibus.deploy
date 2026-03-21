<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class GeocodingService
{
    private function httpClient()
    {
        $timeout = (int) config('services.geocoding.timeout', 10);
        $sslVerify = (bool) config('services.geocoding.ssl_verify', true);

        return Http::timeout($timeout)
            ->withOptions([
                'verify' => $sslVerify,
            ]);
    }

    public function fetchCepData(string $cep): array
    {
        $normalizedCep = preg_replace('/\D/', '', $cep);
        $cacheKey = "geo_cep_{$normalizedCep}";

        return Cache::remember($cacheKey, now()->addMinutes(15), function () use ($normalizedCep) {
            $response = $this->httpClient()->get("https://brasilapi.com.br/api/cep/v2/{$normalizedCep}");

            if ($response->failed()) {
                throw new \RuntimeException('Nao foi possivel consultar o CEP informado.');
            }

            return $response->json();
        });
    }

    public function geocodeAddress(string $address): array
    {
        $cacheKey = 'geo_addr_'.md5($address);

        return Cache::remember($cacheKey, now()->addMinutes(30), function () use ($address) {
            $response = $this->httpClient()
                ->withHeaders([
                    'User-Agent' => 'Omnibus-App/1.0',
                ])
                ->get('https://nominatim.openstreetmap.org/search', [
                    'q' => $address,
                    'format' => 'json',
                    'limit' => 1,
                    'countrycodes' => 'br',
                ]);

            if ($response->failed()) {
                throw new \RuntimeException('Nao foi possivel geocodificar o endereco.');
            }

            $results = $response->json();
            if (!is_array($results) || empty($results)) {
                throw new \RuntimeException('Endereco nao encontrado.');
            }

            $first = $results[0];

            return [
                'address' => $first['display_name'] ?? $address,
                'lat' => (float) ($first['lat'] ?? 0),
                'lng' => (float) ($first['lon'] ?? 0),
            ];
        });
    }

    public function getAddressesByCep(string $cep): array
    {
        $cepData = $this->fetchCepData($cep);

        $street = trim((string) ($cepData['street'] ?? ''));
        $neighborhood = trim((string) ($cepData['neighborhood'] ?? ''));
        $city = trim((string) ($cepData['city'] ?? ''));
        $state = trim((string) ($cepData['state'] ?? ''));
        $normalizedCep = preg_replace('/\D/', '', $cep);

        $queries = array_filter([
            "{$street}, {$neighborhood}, {$city}, {$state}, {$normalizedCep}",
            "{$neighborhood}, {$city}, {$state}, {$normalizedCep}",
            "{$city}, {$state}, {$normalizedCep}",
        ]);

        $options = [];

        foreach ($queries as $query) {
            $response = $this->httpClient()
                ->withHeaders([
                    'User-Agent' => 'Omnibus-App/1.0',
                ])
                ->get('https://nominatim.openstreetmap.org/search', [
                    'q' => $query,
                    'format' => 'json',
                    'limit' => 5,
                    'countrycodes' => 'br',
                ]);

            if ($response->ok() && is_array($response->json())) {
                foreach ($response->json() as $item) {
                    $address = (string) ($item['display_name'] ?? '');
                    $lat = (float) ($item['lat'] ?? 0);
                    $lng = (float) ($item['lon'] ?? 0);
                    $key = md5("{$address}|{$lat}|{$lng}");

                    $options[$key] = [
                        'address' => $address,
                        'lat' => $lat,
                        'lng' => $lng,
                    ];
                }
            }
        }

        if (empty($options) && !empty($street) && !empty($city) && !empty($state)) {
            $formatted = trim("{$street}, {$neighborhood}, {$city} - {$state}, {$normalizedCep}", ', ');

            $options[md5($formatted)] = [
                'address' => $formatted,
                'lat' => 0,
                'lng' => 0,
            ];
        }

        if (empty($options) && !empty($street) && !empty($city)) {
            $fallbackAddress = "{$street}, {$neighborhood}, {$city} - {$state}, {$normalizedCep}";
            try {
                $fallback = $this->geocodeAddress($fallbackAddress);
                $options[md5($fallbackAddress)] = $fallback;
            } catch (\Throwable $e) {
                // Ignora fallback se nao encontrar.
            }
        }

        return array_values($options);
    }
}
