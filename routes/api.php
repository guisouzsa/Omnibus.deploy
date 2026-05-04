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
    
    // Rotas de rotas para motoristas (acompanhar rota)
    Route::get('routes', [App\Http\Controllers\DriverRoutesController::class, 'index']);
    Route::get('routes/{id}', [App\Http\Controllers\DriverRoutesController::class, 'show']);
    
    // Rotas de notificações do motorista
    Route::get('notifications', [App\Http\Controllers\NotificationsController::class, 'index']);
    Route::post('notifications', [App\Http\Controllers\NotificationsController::class, 'store']);
    Route::get('notifications/unread-count', [App\Http\Controllers\NotificationsController::class, 'unreadCount']);
    Route::get('notifications/route/{routeId}', [App\Http\Controllers\NotificationsController::class, 'showByRoute']);
    Route::get('notifications/{id}', [App\Http\Controllers\NotificationsController::class, 'show']);
    Route::put('notifications/{id}/read', [App\Http\Controllers\NotificationsController::class, 'markAsRead']);
    Route::post('notifications/mark-all-read', [App\Http\Controllers\NotificationsController::class, 'markAllAsRead']);
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
    Route::get('routes-distance-chart', [RouteController::class, 'distanceChart']);
    Route::apiResource('schools', App\Http\Controllers\SchoolController::class);
    Route::apiResource('vehicles', App\Http\Controllers\VehicleController::class);
    // Profile update for secretaria (foto + infos)
    Route::patch('user/profile', [App\Http\Controllers\ProfileController::class, 'update']);
    
    // Rotas de notificações para secretárias
    Route::prefix('notifications')->group(function () {
        Route::get('/', [App\Http\Controllers\NotificationsController::class, 'index']);
        Route::get('/unread-count', [App\Http\Controllers\NotificationsController::class, 'unreadCount']);
        Route::get('/{id}', [App\Http\Controllers\NotificationsController::class, 'show']);
        Route::put('/{id}/read', [App\Http\Controllers\NotificationsController::class, 'markAsRead']);
        Route::post('/mark-all-read', [App\Http\Controllers\NotificationsController::class, 'markAllAsRead']);
    });
});