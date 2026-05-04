<?php

// Debug script para verificar rotas
require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$routes = \App\Models\Route::where('user_id', 1)->get();

echo "=== ROTAS DO USUÁRIO 1 ===\n";
foreach ($routes as $route) {
    echo "ID: {$route->id} | Nome: {$route->name} | Distância: {$route->distance}km\n";
}

echo "\nTotal: " . count($routes) . " rotas\n";
