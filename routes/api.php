<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GeocodingController;
use App\Http\Controllers\RouteController;

require __DIR__.'/auth.php';

// Rotas públicas para motoristas (login)
Route::prefix('drivers')->group(function () {
    Route::post('/login', [App\Http\Controllers\DriverAuthController::class, 'login']);
});

// Rotas protegidas para motoristas (app mobile)
Route::middleware(['auth:sanctum'])->prefix('drivers')->group(function () {
    Route::get('/me', [App\Http\Controllers\DriverAuthController::class, 'me']);
    Route::post('/logout', [App\Http\Controllers\DriverAuthController::class, 'logout']);
    Route::post('/logout-all', [App\Http\Controllers\DriverAuthController::class, 'logoutAll']);
    
    // Rotas de despesas do motorista (apenas visualizar e cadastrar)
    Route::apiResource('expenses', App\Http\Controllers\DriverExpensesController::class)->only(['index', 'store', 'show']);
    Route::get('expenses-monthly-total', [App\Http\Controllers\DriverExpensesController::class, 'monthlyTotal']);
});

// Rotas protegidas para secretaria (web)

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::get('spending-limits/user/{userId}', [App\Http\Controllers\SpendingLimitController::class, 'byUser']);
    Route::get('spending-limits/user/{userId}/period/{year}/{month}', [App\Http\Controllers\SpendingLimitController::class, 'byPeriod']);
    Route::get('spending-limits/check/{userId}/{year}/{month}', [App\Http\Controllers\SpendingLimitController::class, 'checkExceeded']);
    Route::post('geocode/addresses-by-cep', [GeocodingController::class, 'addressesByCep']);
    Route::post('geocode/address', [GeocodingController::class, 'geocodeAddress']);

    Route::apiResource('drivers', App\Http\Controllers\DriversController::class);
    Route::get('expenses/summary', [App\Http\Controllers\ExpensesController::class, 'summary']);
    Route::apiResource('expenses', App\Http\Controllers\ExpensesController::class)->only(['index', 'show', 'destroy']);
    Route::apiResource('spending-limits', App\Http\Controllers\SpendingLimitController::class);
    Route::apiResource('routes', RouteController::class);
    Route::apiResource('vehicles', App\Http\Controllers\VehicleController::class);
});