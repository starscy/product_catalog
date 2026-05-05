<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Product\StoreProductRequest;
use App\Http\Requests\Product\UpdateProductRequest;
use App\Models\Product;
use App\Services\ProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Продукты
 */
class ProductController extends Controller
{
    public function __construct(
        private readonly ProductService $products
    )
    {
    }

    /**
     * GET /api/products
     * Публичный: список товаров с фильтрацией
     */
    public function index(Request $request): JsonResponse
    {
        $filters = $this->extractFilters($request);

        $products = $this->products->getFiltered(
            $filters,
            $request->get('per_page', 12)
        );

        return response()->json($products);
    }

    /**
     * GET /api/products/{product}
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
    public function store(StoreProductRequest $request): JsonResponse
    {
        $product = $this->products->create($request->validated());

        return response()->json($product->load('category'), 201);
    }

    /**
     * PUT/PATCH /api/products/{product}
     */
    public function update(UpdateProductRequest $request, Product $product): JsonResponse
    {
        $product = $this->products->update($product, $request->validated());

        return response()->json($product->load('category'));
    }

    /**
     * DELETE /api/products/{product}
     * Мягкое удаление
     */
    public function destroy(Product $product): JsonResponse
    {
        $this->products->delete($product);

        return response()->json(null, 204);
    }

    /**
     * GET /api/products/trashed
     * Только для админа: список удалённых товаров
     */
    public function trashed(Request $request): JsonResponse
    {
        $filters = $this->extractFilters($request);

        $products = $this->products->getTrashed(
            $filters,
            $request->get('per_page', 12)
        );

        return response()->json($products);
    }

    /**
     * POST /api/products/{product}/restore
     */
    public function restore($productId): JsonResponse
    {
        $product = Product::withTrashed()->findOrFail($productId);

        if (!$product->trashed()) {
            return response()->json(['message' => 'Товар не был удалён'], 400);
        }

        $this->products->restore($product);

        return response()->json([
            'message' => 'Товар восстановлен',
            'product' => $product->load('category')
        ]);
    }

    /**
     * DELETE /api/products/{product}/force
     */
    public function forceDelete($productId): JsonResponse
    {
        $product = Product::withTrashed()->findOrFail($productId);

        $this->products->forceDelete($product);

        return response()->json(['message' => 'Товар полностью удалён']);
    }

    /**
     * Извлечь параметры фильтрации из запроса
     */
    private function extractFilters(Request $request): array
    {
        return [
            'category_id' => $request->get('category_id'),
            'search' => $request->get('search'),
            'sort_by' => $request->get('sort_by'),
            'direction' => $request->get('direction'),
        ];
    }
}
