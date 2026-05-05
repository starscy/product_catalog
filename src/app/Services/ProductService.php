<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class ProductService
{
    /**
     * Получить список активных товаров с фильтрацией
     */
    public function getFiltered(array $filters = [], int $perPage = 12): LengthAwarePaginator
    {
        return Product::with('category')
            ->when($filters['category_id'] ?? null, fn(Builder $q, $id) =>
            $q->where('category_id', $id)
            )
            ->when($filters['search'] ?? null, fn(Builder $q, $search) =>
            $q->where(function(Builder $sub) use ($search) {
                $sub->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('description', 'LIKE', "%{$search}%");
            })
            )
            ->when($filters['sort_by'] ?? null, function(Builder $q) use ($filters) {
                $direction = in_array($filters['direction'] ?? null, ['asc', 'desc'])
                    ? $filters['direction']
                    : 'asc';
                $q->orderBy($filters['sort_by'], $direction);
            }, fn(Builder $q) => $q->latest())
            ->paginate($perPage);
    }

    /**
     * Получить список удалённых товаров
     */
    public function getTrashed(array $filters = [], int $perPage = 12): LengthAwarePaginator
    {
        return Product::with('category')
            ->onlyTrashed() // ← только мягко удалённые
            ->when($filters['search'] ?? null, fn(Builder $q, $search) =>
            $q->where(function(Builder $sub) use ($search) {
                $sub->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('description', 'LIKE', "%{$search}%");
            })
            )
            ->latest('deleted_at')
            ->paginate($perPage);
    }

    /**
     * Создать новый товар
     */
    public function create(array $data): Product
    {
        return Product::create($data);
    }

    /**
     * Обновить существующий товар
     */
    public function update(Product $product, array $data): Product
    {
        $product->update($data);
        return $product->fresh();
    }

    /**
     * Мягкое удаление товара
     */
    public function delete(Product $product): bool
    {
        return $product->delete(); // ← с SoftDeletes это мягкое удаление
    }

    /**
     * Восстановить удалённый товар
     */
    public function restore(Product $product): bool
    {
        return $product->restore();
    }

    /**
     * Полное удаление товара
     */
    public function forceDelete(Product $product): bool
    {
        return $product->forceDelete();
    }

    /**
     * Получить товар с категорией (включая удалённые)
     */
    public function findById(int $id, bool $withTrashed = false): ?Product
    {
        $query = Product::with('category');

        if ($withTrashed) {
            $query->withTrashed();
        }

        return $query->find($id);
    }
}
