<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
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
            'name' => [
                'required',
                'string',
                'max:255',
                'min:2',
            ],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                'unique:users',
            ],
            'password' => [
                'required',
                'string',
                'confirmed',
                Password::min(3),
            ],
        ];
    }

    /**
     * Кастомные сообщения об ошибках.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Введите ваше имя',
            'name.min' => 'Имя должно содержать не менее 2 символов',
            'email.required' => 'Введите адрес электронной почты',
            'email.unique' => 'Пользователь с таким email уже существует',
            'password.required' => 'Введите пароль',
            'password.confirmed' => 'Пароли не совпадают',
            'password.min' => 'Пароль должен содержать не менее 3 символов',
            'password.letters' => 'Пароль должен содержать хотя бы одну букву',
            'password.mixed_case' => 'Пароль должен содержать буквы в разных регистрах',
            'password.numbers' => 'Пароль должен содержать хотя бы одну цифру',
        ];
    }

    /**
     * Подготовка данных перед валидацией.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'email' => strtolower(trim($this->email)),
            'name' => trim($this->name),
        ]);
    }
}
