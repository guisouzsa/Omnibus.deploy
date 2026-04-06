<?php

namespace App\Http\Controllers;

use App\Models\SpendingLimit;
use App\Http\Requests\StoreSpendingLimitRequest;
use App\Http\Requests\UpdateSpendingLimitRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SpendingLimitController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = max(1, min((int) $request->query('per_page', 15), 100));
        $limits = SpendingLimit::where('user_id', $request->user()->id)
            ->with('user')
            ->latest()
            ->paginate($perPage);
        return response()->json($limits, 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreSpendingLimitRequest $request): JsonResponse
    {
        $spendingLimit = SpendingLimit::create($request->validated());
        $spendingLimit->load('user');
        
        return response()->json([
            'message' => 'Limite de gastos cadastrado com sucesso.',
            'data' => $spendingLimit
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        $spendingLimit = SpendingLimit::where('user_id', request()->user()->id)
            ->with('user')
            ->findOrFail($id);
        return response()->json($spendingLimit, 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateSpendingLimitRequest $request, string $id): JsonResponse
    {
        $spendingLimit = SpendingLimit::where('user_id', $request->user()->id)->findOrFail($id);
        $spendingLimit->update($request->validated());
        $spendingLimit->load('user');
        
        return response()->json([
            'message' => 'Limite de gastos atualizado com sucesso.',
            'data' => $spendingLimit
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        $spendingLimit = SpendingLimit::where('user_id', request()->user()->id)->findOrFail($id);
        $spendingLimit->delete();
        
        return response()->json([
            'message' => 'Limite de gastos deletado com sucesso.'
        ], 200);
    }

    /**
     * Display a listing of limits by user.
     */
    public function byUser(Request $request, string $userId): JsonResponse
    {
        $perPage = max(1, min((int) $request->query('per_page', 15), 100));

        $limits = SpendingLimit::with('user')
            ->where('user_id', $userId)
            ->latest()
            ->paginate($perPage);

        return response()->json($limits, 200);
    }

    /**
     * Display the spending limit for a specific user and period.
     */
    public function byPeriod(string $userId, string $year, string $month): JsonResponse
    {
        $normalizedMonth = str_pad($month, 2, '0', STR_PAD_LEFT);

        $limit = SpendingLimit::with('user')
            ->where('user_id', $userId)
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', (int) $normalizedMonth)
            ->latest()
            ->first();

        if (!$limit) {
            return response()->json([
                'message' => 'Limite não encontrado para o período informado.'
            ], 404);
        }

        return response()->json($limit, 200);
    }

    /**
     * Check if spending limit was exceeded for a specific user and period.
     */
    public function checkExceeded(string $userId, string $year, string $month): JsonResponse
    {
        $normalizedMonth = str_pad($month, 2, '0', STR_PAD_LEFT);

        $limit = SpendingLimit::with('user')
            ->where('user_id', $userId)
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', (int) $normalizedMonth)
            ->latest()
            ->first();

        if (!$limit) {
            return response()->json([
                'exceeded' => false,
                'limit' => null,
                'message' => 'Nenhum limite cadastrado para o período informado.'
            ], 200);
        }

        return response()->json([
            'exceeded' => (bool) $limit->is_exceeded,
            'limit' => $limit,
        ], 200);
    }
}
