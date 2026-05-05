<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class ProductService
{
    /**
     * Получить список товаров с фильтрацией и пагинацией
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
     * Удалить товар (мягкое удаление)
     */
    public function delete(Product $product): bool
    {
        return $product->delete();
    }

    /**
     * Восстановить удалённый товар
     */
    public function restore(Product $product): bool
    {
        return $product->restore();
    }

    /**
     * Полностью удалить товар (без возможности восстановления)
     */
    public function forceDelete(Product $product): bool
    {
        return $product->forceDelete();
    }

    /**
     * Получить товар с категорией
     */
    public function findById(int $id): ?Product
    {
        return Product::with('category')->find($id);
    }

    /**
     * Получить список удалённых товаров
     */
    public function getTrashed(array $filters = [], int $perPage = 12): LengthAwarePaginator
    {
        return Product::with('category')
            ->onlyTrashed()
            ->when($filters['search'] ?? null, fn(Builder $q, $search) =>
            $q->where(function(Builder $sub) use ($search) {
                $sub->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('description', 'LIKE', "%{$search}%");
            })
            )
            ->latest('deleted_at')
            ->paginate($perPage);
    }
}
