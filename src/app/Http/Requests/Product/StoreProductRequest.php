<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
            ],
            'description' => [
                'required',
                'string',
                'min:10',
                'max:10000',
            ],
            'price' => [
                'required',
                'numeric',
                'min:0.01',
                'max:999999.99',
                'regex:/^\d+(\.\d{1,2})?$/',
            ],
            'category_id' => [
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
            'category_id.exists' => 'Выбранная категория не существует',
            'description.min' => 'Описание должно содержать не менее 10 символов',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'price' => round(floatval($this->price), 2),
            'name' => trim($this->name),
            'description' => trim($this->description),
        ]);
    }
}
