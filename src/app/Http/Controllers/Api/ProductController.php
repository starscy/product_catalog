<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ProductController extends Controller
{
    /**
     * GET /api/products
     * Публичный: список товаров с фильтрацией и пагинацией
     */
    public function index(Request $request): JsonResponse
    {
        $query = Product::with('category');

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('search')) {
            $query->where('name', 'like', "%{$request->search}%");
        }

        if ($request->filled('sort_by')) {
            $direction = in_array($request->get('direction'), ['asc', 'desc'])
                ? $request->get('direction')
                : 'asc';
            $query->orderBy($request->sort_by, $direction);
        } else {
            $query->latest();
        }

        $products = $query->paginate($request->get('per_page', 12));

        return response()->json($products);
    }

    /**
     * GET /api/products/{product}
     * Публичный: детально о товаре
     */
    public function show(Product $product): JsonResponse
    {
        $product->load('category');

        return response()->json($product);
    }

    /**
     * POST /api/products
     * Защищённый: создание товара
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0|max:999999.99',
            'category_id' => 'required|exists:categories,id',
        ]);

        $product = Product::create($validated);

        return response()->json($product->load('category'), 201);
    }

    /**
     * PUT/PATCH /api/products/{product}
     * Защищённый: обновление товара
     */
    public function update(Request $request, Product $product): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'price' => 'sometimes|required|numeric|min:0|max:999999.99',
            'category_id' => 'sometimes|required|exists:categories,id',
        ]);

        $product->update($validated);

        return response()->json($product->load('category'));
    }

    /**
     * DELETE /api/products/{product}
     * Защищённый: удаление товара
     */
    public function destroy(Product $product): JsonResponse
    {
        $product->delete();

        return response()->json(null, 204);
    }
}
