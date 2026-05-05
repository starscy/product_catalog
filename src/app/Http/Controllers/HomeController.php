<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;

class HomeController extends Controller
{
    // app/Http/Controllers/HomeController.php

    public function index(Request $request)
    {
        // 1. Валидация входных параметров (защита + очистка)
        $validated = $request->validate([
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'sort_by'     => ['nullable', 'string', 'in:name,price,created_at'],
            'direction'   => ['nullable', 'string', 'in:asc,desc'],
            'page'        => ['nullable', 'integer', 'min:1'],
        ]);

        // 2. Базовый запрос
        $query = Product::with('category');

        // 3. Фильтр по категории
        if (!empty($validated['category_id'])) {
            $query->where('category_id', $validated['category_id']);
        }

        // 4. Сортировка (безопасная, через валидированные значения)
        if (!empty($validated['sort_by'])) {
            $direction = $validated['direction'] ?? 'asc';
            $query->orderBy($validated['sort_by'], $direction);
        } else {
            $query->latest(); // сортировка по умолчанию
        }

        // 5. Пагинация + сохранение параметров в ссылках
        $products = $query->paginate(12)->withQueryString();
        $categories = Category::all();

        $isAdmin = str_contains($request->path(), 'admin');

        // 6. Возвращаем фильтры для синхронизации с фронтендом
        return Inertia::render('Products/Index', [
            'products' => $products,
            'categories' => $categories,
            'filters' => [
                'category_id' => $validated['category_id'] ?? null,
                'sort_by' => $validated['sort_by'] ?? null,
                'direction' => $validated['direction'] ?? 'asc',
            ]
        ]);
    }

    public function adminIndex(Request $request)
    {
        $validated = $request->validate([
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'search'      => ['nullable', 'string', 'max:255'],
            'trashed'     => ['nullable', 'boolean'], // ← добавьте это
            'page'        => ['nullable', 'integer', 'min:1'],
        ]);

        // Базовый запрос
        $query = Product::with('category');

        // 🔍 Если показываем удалённые
        if (!empty($validated['trashed'])) {
            $query->onlyTrashed(); // только удалённые
        }

        // Фильтр по категории
        if (!empty($validated['category_id'])) {
            $query->where('category_id', $validated['category_id']);
        }

        // Поиск
        if (!empty($validated['search'])) {
            $search = "%{$validated['search']}%";
            $query->where(function($q) use ($search) {
                $q->where('name', 'LIKE', $search)
                    ->orWhere('description', 'LIKE', $search);
            });
        }

        $products = $query->latest('deleted_at')->paginate(12)->withQueryString();

        return \Inertia\Inertia::render('Admin/Products/Index', [
            'products' => $products,
            'filters' => [
                'search' => $validated['search'] ?? null,
                'trashed' => $validated['trashed'] ?? null,
                'category_id' => $validated['category_id'] ?? null,
            ]
        ]);
    }

    // Детальная страница товара
    public function show(Product $product)
    {
        // Загружаем категорию (если есть связь)
        $product->load('category');

        return Inertia::render('Products/Show', [
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'description' => $product->description,
                'price' => $product->price,
                'category' => $product->category?->only(['id', 'name']),
                'created_at' => $product->created_at,
            ]
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
