<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\AuthController;

// Публичные эндпоинты
Route::get('/categories', [CategoryController::class, 'index']);
Route::apiResource('products', ProductController::class)->only(['index', 'show']);

// Защищённые эндпоинты
Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('products', ProductController::class)->except(['index', 'show']);

    Route::get('/products/trashed', [ProductController::class, 'trashed'])
        ->name('products.trashed');

    Route::post('/products/{product}/restore', [ProductController::class, 'restore'])
        ->name('products.restore');

    Route::delete('/products/{product}/force', [ProductController::class, 'forceDelete'])
        ->name('products.force-delete');
});

// Аутентификация
Route::post('/login', [AuthController::class, 'login']);
