<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\AuthController;

// Публичные эндпоинты (только чтение)
Route::get('/categories', [CategoryController::class, 'index']);
Route::apiResource('products', ProductController::class)->only(['index', 'show']);

// Защищённые эндпоинты (требуют токен)
Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('products', ProductController::class)->except(['index', 'show']);
    Route::post('/logout', [AuthController::class, 'logout']);
});

// Аутентификация (отдельно, без middleware)
Route::post('/login', [AuthController::class, 'login']);
