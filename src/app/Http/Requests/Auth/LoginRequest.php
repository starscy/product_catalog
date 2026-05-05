<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    /**
     * Определяет, авторизован ли пользователь для этого запроса.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Правила валидации.
     */
    public function rules(): array
    {
        return [
            'email' => [
                'required',
                'email',
                'max:255',
            ],
            'password' => [
                'required',
                'string',
                'min:3',
            ],
        ];
    }

    /**
     * Кастомные сообщения об ошибках.
     */
    public function messages(): array
    {
        return [
            'email.required' => 'Введите адрес электронной почты',
            'email.email' => 'Введите корректный адрес электронной почты',
            'email.max' => 'Адрес электронной почты не должен превышать 255 символов',
            'password.required' => 'Введите пароль',
            'password.min' => 'Пароль должен содержать не менее 6 символов',
        ];
    }

    /**
     * Подготовка данных перед валидацией.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'email' => strtolower(trim($this->email)),
        ]);
    }
}
