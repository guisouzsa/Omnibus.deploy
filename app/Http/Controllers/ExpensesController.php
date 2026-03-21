<?php

namespace App\Http\Controllers;

use App\Models\Expenses;
use App\Models\SpendingLimit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExpensesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $expenses = Expenses::with('driver')->latest()->get();
        return response()->json($expenses, 200);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        $expense = Expenses::with('driver')->findOrFail($id);
        return response()->json($expense, 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        $expense = Expenses::findOrFail($id);
        $expense->delete();
        
        return response()->json([
            'message' => 'Despesa deletada com sucesso.'
        ], 200);
    }

    /**
     * Retorna resumo de gastos para o dashboard da secretaria.
     */
    public function summary(Request $request): JsonResponse
    {
        $userId = (int) $request->user()->id;
        $currentMonth = (int) now()->month;
        $currentYear = (int) now()->year;

        $currentMonthLimit = SpendingLimit::query()
            ->where('user_id', $userId)
            ->whereYear('created_at', $currentYear)
            ->whereMonth('created_at', $currentMonth)
            ->latest()
            ->value('limit_amount');

        $monthly = Expenses::query()
            ->join('drivers', 'drivers.id', '=', 'expenses.driver_id')
            ->where('drivers.user_id', $userId)
            ->selectRaw('EXTRACT(YEAR FROM expenses.created_at) AS year')
            ->selectRaw('EXTRACT(MONTH FROM expenses.created_at) AS month')
            ->selectRaw('SUM(expenses.value) AS total')
            ->groupByRaw('EXTRACT(YEAR FROM expenses.created_at), EXTRACT(MONTH FROM expenses.created_at)')
            ->get();

        $currentMonthExpenses = 0.0;
        $minMonthExpenses = null;

        foreach ($monthly as $row) {
            $year = (int) $row->year;
            $month = (int) $row->month;
            $total = (float) $row->total;

            if ($year === $currentYear && $month === $currentMonth) {
                $currentMonthExpenses = $total;
            }

            if ($minMonthExpenses === null || $total < $minMonthExpenses) {
                $minMonthExpenses = $total;
            }
        }

        return response()->json([
            'current_month_expenses' => $currentMonthExpenses,
            'min_month_expenses' => $minMonthExpenses ?? 0.0,
            'current_month_limit' => (float) ($currentMonthLimit ?? 0),
        ], 200);
    }
}
