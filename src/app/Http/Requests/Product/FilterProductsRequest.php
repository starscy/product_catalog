<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;

class FilterProductsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'sort_by' => ['nullable', 'string', 'in:name,price,created_at'],
            'direction' => ['nullable', 'string', 'in:asc,desc'],
            'page' => ['nullable', 'integer', 'min:1'],
        ];
    }

    public function messages(): array
    {
        return [
            'category_id.exists' => 'Категория не найдена',
            'sort_by.in' => 'Недопустимое поле сортировки',
            'direction.in' => 'Направление должно быть asc или desc',
        ];
    }

    // Геттеры для чистого кода в контроллере
    public function categoryId(): ?int
    {
        return $this->integer('category_id');
    }

    public function sortBy(): ?string
    {
        return $this->string('sort_by');
    }

    public function direction(): string
    {
        return $this->input('direction', 'asc');
    }

    public function page(): ?int
    {
        return $this->integer('page');
    }
}
