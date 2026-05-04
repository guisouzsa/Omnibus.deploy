<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRouteRequest;
use App\Http\Requests\UpdateRouteRequest;
use App\Models\Route;
use App\Models\School;
use App\Services\GeocodingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RouteController extends Controller
{
    public function __construct(private GeocodingService $geocodingService)
    {
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = max(1, min((int) $request->query('per_page', 15), 100));

        $routes = Route::where('user_id', $request->user()->id)
            ->with('school')
            ->latest()
            ->paginate($perPage);

        return response()->json($routes, 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRouteRequest $request): JsonResponse
    {
        $data = $request->validated();

        if (!empty($data['school_id'])) {
            $school = School::where('user_id', $request->user()->id)->find($data['school_id']);

            if (!$school) {
                return response()->json([
                    'message' => 'Escola informada nao pertence ao usuario autenticado.',
                ], 422);
            }

            $data['end_point'] = $school->address;
            $data['end_point_lat'] = $school->lat;
            $data['end_point_lng'] = $school->lng;
        }

        if (
            empty($data['start_point_lat']) &&
            !empty($data['start_point'])
        ) {
            try {
                $geo = $this->geocodingService->geocodeAddress($data['start_point']);
                $data['start_point_lat'] = $geo['lat'];
                $data['start_point_lng'] = $geo['lng'];
            } catch (\Throwable $e) {
                // Mantem sem coordenadas caso geocoding falhe.
            }
        }

        if (
            empty($data['end_point_lat']) &&
            !empty($data['end_point'])
        ) {
            try {
                $geo = $this->geocodingService->geocodeAddress($data['end_point']);
                $data['end_point_lat'] = $geo['lat'];
                $data['end_point_lng'] = $geo['lng'];
            } catch (\Throwable $e) {
                // Mantem sem coordenadas caso geocoding falhe.
            }
        }

        $data['distance'] = $this->calculateDistanceKm(
            $data['start_point_lat'] ?? null,
            $data['start_point_lng'] ?? null,
            $data['end_point_lat'] ?? null,
            $data['end_point_lng'] ?? null
        );

        $route = Route::create([
            ...$data,
            'user_id' => $request->user()->id,
        ])->load('school');

        return response()->json([
            'message' => 'Rota cadastrada com sucesso.',
            'data' => $route,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $id): JsonResponse
    {
        $route = Route::where('user_id', $request->user()->id)->with('school')->findOrFail($id);

        $duration = $this->estimateDurationMinutes(
            $route->start_point_lat,
            $route->start_point_lng,
            $route->end_point_lat,
            $route->end_point_lng
        );

        return response()->json([
            'data' => $route,
            'suggested_duration_minutes' => $duration,
            'map_points' => [
                'start' => [
                    'lat' => $route->start_point_lat,
                    'lng' => $route->start_point_lng,
                    'label' => $route->start_point,
                ],
                'end' => [
                    'lat' => $route->end_point_lat,
                    'lng' => $route->end_point_lng,
                    'label' => $route->end_point,
                ],
            ],
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRouteRequest $request, string $id): JsonResponse
    {
        $route = Route::where('user_id', $request->user()->id)->with('school')->findOrFail($id);
        $data = $request->validated();

        if (array_key_exists('school_id', $data)) {
            if (empty($data['school_id'])) {
                $data['school_id'] = null;
            } else {
                $school = School::where('user_id', $request->user()->id)->find($data['school_id']);

                if (!$school) {
                    return response()->json([
                        'message' => 'Escola informada nao pertence ao usuario autenticado.',
                    ], 422);
                }

                $data['end_point'] = $school->address;
                $data['end_point_lat'] = $school->lat;
                $data['end_point_lng'] = $school->lng;
            }
        }

        if (
            empty($data['start_point_lat']) &&
            !empty($data['start_point'])
        ) {
            try {
                $geo = $this->geocodingService->geocodeAddress($data['start_point']);
                $data['start_point_lat'] = $geo['lat'];
                $data['start_point_lng'] = $geo['lng'];
            } catch (\Throwable $e) {
                // Mantem sem coordenadas caso geocoding falhe.
            }
        }

        if (
            empty($data['end_point_lat']) &&
            !empty($data['end_point'])
        ) {
            try {
                $geo = $this->geocodingService->geocodeAddress($data['end_point']);
                $data['end_point_lat'] = $geo['lat'];
                $data['end_point_lng'] = $geo['lng'];
            } catch (\Throwable $e) {
                // Mantem sem coordenadas caso geocoding falhe.
            }
        }

        // Calcular distância se coordenadas foram atualizadas
        if (isset($data['start_point_lat']) || isset($data['start_point_lng']) || 
            isset($data['end_point_lat']) || isset($data['end_point_lng'])) {
            $data['distance'] = $this->calculateDistanceKm(
                $data['start_point_lat'] ?? $route->start_point_lat,
                $data['start_point_lng'] ?? $route->start_point_lng,
                $data['end_point_lat'] ?? $route->end_point_lat,
                $data['end_point_lng'] ?? $route->end_point_lng
            );
        }

        $route->update($data);
        $route->load('school');

        return response()->json([
            'message' => 'Rota atualizada com sucesso.',
            'data' => $route,
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        $route = Route::where('user_id', $request->user()->id)->findOrFail($id);
        $route->delete();

        return response()->json([
            'message' => 'Rota removida com sucesso.',
        ], 200);
    }

    private function estimateDurationMinutes(
        ?float $startLat,
        ?float $startLng,
        ?float $endLat,
        ?float $endLng
    ): ?int {
        if (!$startLat || !$startLng || !$endLat || !$endLng) {
            return null;
        }

        $earthKm = 6371;
        $dLat = deg2rad($endLat - $startLat);
        $dLng = deg2rad($endLng - $startLng);

        $a = sin($dLat / 2) * sin($dLat / 2)
            + cos(deg2rad($startLat)) * cos(deg2rad($endLat))
            * sin($dLng / 2) * sin($dLng / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        $distanceKm = $earthKm * $c;

        $averageSpeedKmH = 30;
        $hours = $distanceKm / $averageSpeedKmH;

        return (int) max(1, round($hours * 60));
    }

    private function calculateDistanceKm(
        ?float $startLat,
        ?float $startLng,
        ?float $endLat,
        ?float $endLng
    ): ?float {
        if (!$startLat || !$startLng || !$endLat || !$endLng) {
            return null;
        }

        try {
            $osrmUrl = "https://router.project-osrm.org/route/v1/driving/{$startLng},{$startLat};{$endLng},{$endLat}?overview=full";
            
            $response = @file_get_contents($osrmUrl);
            $data = json_decode($response, true);

            if (isset($data['routes'][0]['distance'])) {
                return round($data['routes'][0]['distance'] / 1000, 2);
            }
        } catch (\Throwable $e) {
            // Se OSRM falhar, usar fórmula de Haversine
        }

        // Fallback: calcular usando Haversine (aproximado)
        $earthKm = 6371;
        $dLat = deg2rad($endLat - $startLat);
        $dLng = deg2rad($endLng - $startLng);

        $a = sin($dLat / 2) * sin($dLat / 2)
            + cos(deg2rad($startLat)) * cos(deg2rad($endLat))
            * sin($dLng / 2) * sin($dLng / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        $distanceKm = $earthKm * $c;

        return round($distanceKm, 2);
    }

    /**
     * Retorna dados de distância das rotas para o gráfico do dashboard
     */
    public function distanceChart(Request $request): JsonResponse
    {
        $userId = $request->user()->id;
        
        $routes = Route::where('user_id', $userId)
            ->select('id', 'name', 'distance', 'start_point_lat', 'start_point_lng', 'end_point_lat', 'end_point_lng')
            ->orderByDesc('distance')
            ->get();

        $routes = $routes->map(function ($route) {
            if (is_null($route->distance) && $route->start_point_lat && $route->start_point_lng && 
                $route->end_point_lat && $route->end_point_lng) {
                $calculatedDistance = $this->calculateDistanceKm(
                    $route->start_point_lat,
                    $route->start_point_lng,
                    $route->end_point_lat,
                    $route->end_point_lng
                );
                if ($calculatedDistance) {
                    $route->distance = $calculatedDistance;
                    $route->save();
                }
            }

            return [
                'rota' => $route->name,
                'km' => (float) ($route->distance ?? 0),
            ];
        })->filter(fn($r) => $r['km'] > 0)->values();

        return response()->json([
            'data' => $routes,
        ], 200);
    }
}
