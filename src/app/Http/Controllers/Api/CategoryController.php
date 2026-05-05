<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\JsonResponse;

/**
 * Категория
 */
class CategoryController extends Controller
{
    /**
     * GET /api/categories
     * Публичный: список категорий
     */
    public function index(): JsonResponse
    {
        $categories = Category::withCount('products')->latest()->get();

        return response()->json($categories);
    }
}
