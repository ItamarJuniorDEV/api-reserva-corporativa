<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/teste', function () {
    return ['message' => 'API funcionando!'];
});

Route::post('/login', [AuthController::class, 'login']);