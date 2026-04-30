<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    // Публичная страница со списком товаров
    public function index(Request $request)
    {
        $query = Product::with('category');

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('sort_by')) {
            $direction = $request->get('direction', 'asc');
            $query->orderBy($request->sort_by, $direction);
        }

        $products = $query->paginate(12)->withQueryString();
        $categories = Category::all();

        return Inertia::render('Products/Index', [
            'products' => $products,
            'categories' => $categories,
            'filters' => $request->only(['category_id', 'sort_by', 'direction'])
        ]);
    }

    // Детальная страница товара
    public function show(Product $product)
    {
        $product->load('category');

        return Inertia::render('Products/Show', [
            'product' => $product
        ]);
    }

    public function dashboard()
    {
        $products = Product::with('category')->latest()->paginate(10);
        $categories = Category::all();

        return Inertia::render('Dashboard', [
            'products' => $products,
            'categories' => $categories
        ]);
    }

    // Форма создания товара
    public function create()
    {
        $categories = Category::all();

        return Inertia::render('Products/Create', [
            'categories' => $categories
        ]);
    }

    // Сохранение товара
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0|max:999999.99',
            'category_id' => 'required|exists:categories,id'
        ]);

        $product = Product::create($validated);

        return redirect()->route('dashboard')
            ->with('success', 'Товар успешно создан!');
    }

    // Форма редактирования
    public function edit(Product $product)
    {
        $categories = Category::all();
        $product->load('category');

        return Inertia::render('Products/Edit', [
            'product' => $product,
            'categories' => $categories
        ]);
    }

    // Обновление товара
    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0|max:999999.99',
            'category_id' => 'required|exists:categories,id'
        ]);

        $product->update($validated);

        return redirect()->route('dashboard')
            ->with('success', 'Товар успешно обновлен!');
    }

    // Удаление товара
    public function destroy(Product $product)
    {
        $product->delete();

        return redirect()->route('dashboard')
            ->with('success', 'Товар успешно удален!');
    }
}
