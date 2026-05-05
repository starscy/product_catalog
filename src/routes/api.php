<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\AuthController;

// ==================== ПУБЛИЧНЫЕ ЭНДПОИНТЫ ====================
Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{product}', [ProductController::class, 'show']);

// ==================== АУТЕНТИФИКАЦИЯ ====================
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

// ==================== ЗАЩИЩЁННЫЕ ЭНДПОИНТЫ ====================
Route::middleware('auth:sanctum')->group(function () {
    // CRUD для товаров (кроме index, show)
    Route::post('/products', [ProductController::class, 'store']);
    Route::put('/products/{product}', [ProductController::class, 'update']);
    Route::delete('/products/{product}', [ProductController::class, 'destroy']);

    // Работа с мягким удалением
    Route::get('/products/trashed', [ProductController::class, 'trashed'])->name('products.trashed');
    Route::post('/products/{product}/restore', [ProductController::class, 'restore'])->name('products.restore');
    Route::delete('/products/{product}/force', [ProductController::class, 'forceDelete'])->name('products.force-delete');

    // Выход
    Route::post('/logout', [AuthController::class, 'logout']);
});
