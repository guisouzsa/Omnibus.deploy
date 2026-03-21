<?php

namespace App\Http\Controllers;

use App\Services\GeocodingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GeocodingController extends Controller
{
    public function __construct(private GeocodingService $geocodingService)
    {
    }

    public function addressesByCep(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'cep' => 'required|string|min:8|max:9',
        ]);

        try {
            $addresses = $this->geocodingService->getAddressesByCep($validated['cep']);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'data' => [],
            ], 422);
        }

        return response()->json([
            'data' => $addresses,
        ], 200);
    }

    public function geocodeAddress(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'address' => 'required|string|min:5|max:500',
        ]);

        try {
            $result = $this->geocodingService->geocodeAddress($validated['address']);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'data' => null,
            ], 422);
        }

        return response()->json([
            'data' => $result,
        ], 200);
    }
}
