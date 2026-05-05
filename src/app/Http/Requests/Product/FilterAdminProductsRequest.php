<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;

class FilterAdminProductsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'search' => ['nullable', 'string', 'max:255'],
            'trashed' => ['nullable', 'boolean'],
            'page' => ['nullable', 'integer', 'min:1'],
        ];
    }

    public function messages(): array
    {
        return [
            'search.max' => 'Поиск не должен превышать 255 символов',
            'trashed.boolean' => 'Параметр trashed должен быть true/false',
        ];
    }

    public function wantsOnlyTrashed(): bool
    {
        return $this->boolean('trashed');
    }

    public function searchQuery(): ?string
    {
        return $this->string('search');
    }

    public function categoryId(): ?int
    {
        $value = $this->input('category_id');
        return $value !== null ? (int)$value : null;
    }
}
