<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSchoolRequest;
use App\Http\Requests\UpdateSchoolRequest;
use App\Models\School;
use App\Services\GeocodingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SchoolController extends Controller
{
    public function __construct(private GeocodingService $geocodingService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $perPage = max(1, min((int) $request->query('per_page', 15), 100));

        $schools = School::where('user_id', $request->user()->id)
            ->latest()
            ->paginate($perPage);

        return response()->json($schools, 200);
    }

    public function store(StoreSchoolRequest $request): JsonResponse
    {
        $data = $request->validated();

        if ((empty($data['lat']) || empty($data['lng'])) && !empty($data['address'])) {
            try {
                $geo = $this->geocodingService->geocodeAddress($data['address']);
                $data['lat'] = $geo['lat'];
                $data['lng'] = $geo['lng'];
            } catch (\Throwable $e) {
                // Mantem sem coordenadas caso geocoding falhe.
            }
        }

        $school = School::create([
            ...$data,
            'user_id' => $request->user()->id,
        ]);

        return response()->json([
            'message' => 'Escola cadastrada com sucesso.',
            'data' => $school,
        ], 201);
    }

    public function show(Request $request, string $id): JsonResponse
    {
        $school = School::where('user_id', $request->user()->id)->findOrFail($id);

        return response()->json([
            'data' => $school,
        ], 200);
    }

    public function update(UpdateSchoolRequest $request, string $id): JsonResponse
    {
        $school = School::where('user_id', $request->user()->id)->findOrFail($id);
        $data = $request->validated();

        if ((isset($data['address']) || isset($data['cep'])) && (empty($data['lat']) || empty($data['lng']))) {
            try {
                $geo = $this->geocodingService->geocodeAddress($data['address'] ?? $school->address);
                $data['lat'] = $geo['lat'];
                $data['lng'] = $geo['lng'];
            } catch (\Throwable $e) {
                // Mantem sem coordenadas caso geocoding falhe.
            }
        }

        $school->update($data);

        return response()->json([
            'message' => 'Escola atualizada com sucesso.',
            'data' => $school,
        ], 200);
    }

    public function destroy(Request $request, string $id): JsonResponse
    {
        $school = School::where('user_id', $request->user()->id)->findOrFail($id);
        $school->delete();

        return response()->json([
            'message' => 'Escola removida com sucesso.',
        ], 200);
    }
}
