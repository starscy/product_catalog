<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $productId = $this->route('product')?->id;

        return [
            'name' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                Rule::unique('products', 'name')->ignore($productId),
            ],
            'description' => [
                'sometimes',
                'required',
                'string',
                'min:10',
                'max:10000',
            ],
            'price' => [
                'sometimes',
                'required',
                'numeric',
                'min:0.01',
                'max:999999.99',
                'regex:/^\d+(\.\d{1,2})?$/',
            ],
            'category_id' => [
                'sometimes',
                'required',
                'integer',
                'exists:categories,id',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'price.regex' => 'Цена должна иметь не более 2 знаков после запятой',
            'name.unique' => 'Товар с таким названием уже существует',
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('price')) {
            $this->merge(['price' => round(floatval($this->price), 2)]);
        }
        if ($this->has('name')) {
            $this->merge(['name' => trim($this->name)]);
        }
    }
}
