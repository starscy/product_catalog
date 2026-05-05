<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Http\Requests\Product\FilterProductsRequest;
use App\Http\Requests\Product\FilterAdminProductsRequest;
use App\Http\Requests\Product\StoreProductRequest;
use App\Http\Requests\Product\UpdateProductRequest;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Домашняя страница для всех и админка
 */
class HomeController extends Controller
{
    /**
     * Отображает публичный каталог товаров с фильтрацией и сортировкой.
     */
    public function index(FilterProductsRequest $request): Response
    {
        $query = Product::with('category');

        if ($request->categoryId()) {
            $query->where('category_id', $request->categoryId());
        }

        $query->orderBy(
            $request->sortBy() ?? 'created_at',
            $request->direction()
        );

        $products = $query->paginate(12)->withQueryString();
        $categories = Category::all();

        return Inertia::render('Products/Index', [
            'products' => $products,
            'categories' => $categories,
            'filters' => [
                'category_id' => $request->categoryId(),
                'sort_by' => $request->sortBy(),
                'direction' => $request->direction(),
            ]
        ]);
    }

    /**
     * Отображает админ-панель со списком товаров.
     */
    public function adminIndex(FilterAdminProductsRequest $request): Response
    {
        $query = Product::with('category');

        if ($request->wantsOnlyTrashed()) {
            $query->onlyTrashed();
        }

        if ($request->categoryId()) {
            $query->where('category_id', $request->categoryId());
        }

        if ($request->searchQuery()) {
            $search = "%{$request->searchQuery()}%";
            $query->where(fn($q) => $q
                ->where('name', 'LIKE', $search)
                ->orWhere('description', 'LIKE', $search)
            );
        }

        $products = $query->latest('deleted_at')->paginate(12)->withQueryString();

        return Inertia::render('Admin/Products/Index', [
            'products' => $products,
            'filters' => [
                'search' => $request->searchQuery(),
                'trashed' => $request->boolean('trashed'),
                'category_id' => $request->categoryId(),
            ]
        ]);
    }

    /**
     * Отображает детальную страницу товара.
     */
    public function show(Product $product): Response
    {
        $product->load('category');

        return Inertia::render('Products/Show', [
            'product' => $product->only(['id', 'name', 'description', 'price', 'created_at']) + [
                    'category' => $product->category?->only(['id', 'name']),
                ]
        ]);
    }

    /**
     * Отображает форму создания нового товара.
     */
    public function create(): Response
    {
        return Inertia::render('Products/Create', [
            'categories' => Category::all()
        ]);
    }

    /**
     * Сохраняет новый товар в базе данных.
     *
     */
    public function store(StoreProductRequest $request): RedirectResponse
    {
        Product::create($request->validatedProductData());

        return redirect()->route('dashboard')->with('success', 'Товар создан!');
    }

    /**
     * Отображает форму редактирования существующего товара.
     */
    public function edit(Product $product): Response
    {
        $product->load('category');

        return Inertia::render('Products/Edit', [
            'product' => $product,
            'categories' => Category::all()
        ]);
    }

    /**
     * Обновляет данные существующего товара.
     */
    public function update(UpdateProductRequest $request, Product $product): RedirectResponse
    {
        $product->update($request->validatedProductData());

        return redirect()->route('dashboard')->with('success', 'Товар обновлён!');
    }

    /**
     * Удаляет товар (мягкое удаление / soft delete).
     */
    public function destroy(Product $product): RedirectResponse
    {
        $product->delete();

        return redirect()->route('dashboard')->with('success', 'Товар удалён!');
    }
}
