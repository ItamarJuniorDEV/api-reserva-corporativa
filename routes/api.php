<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\RecursoController;
use App\Http\Controllers\ReservaController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Teste
Route::get('/teste', function () {
    return ['message' => 'API funcionando!'];
});

// Login
Route::post('/login', [AuthController::class, 'login']);

// Rotas protegidas
Route::middleware('auth:sanctum')->group(function() {
    // Recursos
    Route::get('/recursos', [RecursoController::class, 'index']);
    Route::get('/recursos/{id}/disponibilidade', [RecursoController::class, 'disponibilidade']);

    // Reservas
    Route::get('/reservas/minhas', [ReservaController::class, 'minhasReservas']);
    Route::delete('/reservas/{id}', [ReservaController::class, 'destroy']);
});





