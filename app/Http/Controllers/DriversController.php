<?php

namespace App\Http\Controllers;

use App\Models\Drivers;
use App\Http\Requests\StoreDriverRequest;
use App\Http\Requests\UpdateDriverRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class DriversController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $drivers = Drivers::with('user')->paginate(15);
        return response()->json($drivers, 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreDriverRequest $request): JsonResponse
    {
        $data = $request->validated();
        
        // Adiciona o user_id do usuário autenticado
        $data['user_id'] = auth()->id();
        
        $driver = Drivers::create($data);
        $driver->load('user');
        
        return response()->json([
            'message' => 'Motorista cadastrado com sucesso.',
            'data' => $driver
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        $driver = Drivers::with(['user', 'expenses'])->findOrFail($id);
        return response()->json($driver, 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateDriverRequest $request, string $id): JsonResponse
    {
        $driver = Drivers::findOrFail($id);
        $driver->update($request->validated());
        $driver->load('user');
        
        return response()->json([
            'message' => 'Motorista atualizado com sucesso.',
            'data' => $driver
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        $driver = Drivers::findOrFail($id);

        DB::transaction(function () use ($driver) {
            $driver->vehicles()->update(['driver_id' => null]);
            $driver->delete();
        });
        
        return response()->json([
            'message' => 'Motorista deletado com sucesso.'
        ], 200);
    }
}
