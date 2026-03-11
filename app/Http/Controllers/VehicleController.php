<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\StoreVehicleRequest;
use App\Http\Requests\UpdateVehicleRequest;
use Illuminate\Validation\Rule;

class VehicleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $vehicles = Vehicle::with('driver')->get();
        return response()->json($vehicles, 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreVehicleRequest $request): JsonResponse
    {
        $vehicle = Vehicle::create($request->validated());
        $vehicle->load('driver');

        return response()->json([
            'message' => 'Ônibus cadastrado com sucesso.',
            'data' => $vehicle
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        $vehicle = Vehicle::with('driver')->findOrFail($id);
        return response()->json($vehicle, 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateVehicleRequest $request, string $id): JsonResponse
    {
        $vehicle = Vehicle::findOrFail($id);
        $vehicle->update($request->validated());
        $vehicle->load('driver');

        return response()->json([
            'message' => 'Dados do ônibus atualizados com sucesso.',
            'data' => $vehicle
        ], 200);    
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        $vehicle = Vehicle::findOrFail($id);
        $vehicle->delete();

        return response()->json([
            'message' => 'Ônibus deletado com sucesso.'
        ], 200);
    }
}