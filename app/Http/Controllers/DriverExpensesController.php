<?php

namespace App\Http\Controllers;

use App\Models\Expenses;
use App\Http\Requests\StoreDriverExpenseRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DriverExpensesController extends Controller
{
    /**
     * Lista todas as despesas do motorista autenticado
     */
    public function index(Request $request): JsonResponse
    {
        $driver = $request->user();
        $perPage = max(1, min((int) $request->query('per_page', 15), 100));
        $expenses = $driver->expenses()->latest()->paginate($perPage);
        
        return response()->json($expenses, 200);
    }

    /**
     * Cadastra uma nova despesa para o motorista autenticado
     */
    public function store(StoreDriverExpenseRequest $request): JsonResponse
    {
        try {
            $driver = $request->user();
            $validated = $request->validated();

            $proofOfPayment = $validated['proof_of_payment'];

            if ($request->hasFile('proof_of_payment')) {
                $path = $request->file('proof_of_payment')->store('expenses/proofs');
                $proofOfPayment = Storage::url($path);
            }
            
            $expense = Expenses::create([
                'driver_id' => $driver->id,
                'vehicle_plate' => $validated['vehicle_plate'],
                'value' => $validated['value'],
                'proof_of_payment' => $proofOfPayment,
                'description' => $validated['description'] ?? null,
            ]);

            return response()->json([
                'message' => 'Despesa cadastrada com sucesso.',
                'data' => $expense
            ], 201);
        } catch (\Illuminate\Database\QueryException $e) {
            // Erro de banco de dados
            if (str_contains($e->getMessage(), 'Data too long')) {
                return response()->json([
                    'message' => 'O arquivo de comprovante é muito grande. Tente comprimir a imagem.',
                    'error' => 'file_too_large'
                ], 422);
            }
            
            return response()->json([
                'message' => 'Erro ao salvar a despesa. Tente novamente.',
                'error' => 'database_error'
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erro ao processar a despesa.',
                'error' => 'unknown_error'
            ], 500);
        }
    }

    /**
     * Exibe uma despesa específica do motorista autenticado
     */
    public function show(Request $request, string $id): JsonResponse
    {
        $driver = $request->user();
        $expense = $driver->expenses()->findOrFail($id);
        return response()->json($expense, 200);
    }

    /**
     * Retorna o total de despesas do motorista no mês atual
     */
    public function monthlyTotal(Request $request): JsonResponse
    {
        $driver = $request->user();
        
        $total = $driver->expenses()
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('value');

        return response()->json([
            'month' => now()->format('m'),
            'year' => now()->format('Y'),
            'total' => $total
        ], 200);
    }
}
