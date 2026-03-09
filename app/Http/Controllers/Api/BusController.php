<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Bus;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\StoreBusRequest;
use App\Http\Requests\UpdateBusRequest;
use Illuminate\Validation\Rule;

class BusController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $buses = Bus::with('driver')->get();
        return response()->json($buses, 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreBusRequest $request): JsonResponse
    {
        $bus = Bus::create($request->validated());
        $bus->load('driver');

        return response()->json([
            'message' => 'Ônibus cadastrado com sucesso.',
            'data' => $bus
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        $bus = Bus::with('driver')->findOrFail($id);
        return response()->json($bus, 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateBusRequest $request, string $id): JsonResponse
    {
        $bus = Bus::findOrFail($id);
        $bus->update($request->validated());
        $bus->load('driver');

        return response()->json([
            'message' => 'Dados do ônibus atualizados com sucesso.',
            'data' => $bus
        ], 200);    
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        $bus = Bus::findOrFail($id);
        $bus->delete();

        return response()->json([
            'message' => 'Ônibus deletado com sucesso.'
        ], 200);
    }
}
