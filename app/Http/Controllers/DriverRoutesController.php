<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\Route as RouteModel;
use App\Services\GeocodingService;

class DriverRoutesController extends Controller
{
    public function __construct(private GeocodingService $geocodingService)
    {
    }

    /**
     * List routes assigned to the authenticated driver
     */
    public function index(Request $request): JsonResponse
    {
        $driver = $request->user();
        $routes = RouteModel::where('driver_id', $driver->id)
            ->with('school')
            ->latest()
            ->get();

        return response()->json([
            'data' => $routes,
        ], 200);
    }

    /**
     * Show a specific route for the authenticated driver
     */
    public function show(Request $request, string $id): JsonResponse
    {
        $driver = $request->user();
        $route = RouteModel::where('id', $id)
            ->where('driver_id', $driver->id)
            ->with('school')
            ->firstOrFail();

        $duration = null;
        if ($route->start_point_lat && $route->start_point_lng && $route->end_point_lat && $route->end_point_lng) {
            $duration = $this->estimateDurationMinutes(
                $route->start_point_lat,
                $route->start_point_lng,
                $route->end_point_lat,
                $route->end_point_lng
            );
        }

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
}
