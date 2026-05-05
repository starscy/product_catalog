<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class AuthController extends Controller
{
    /**
     * Показать страницу входа.
     */
    public function showLogin()
    {
        return Inertia::render('Auth/Login');
    }

    /**
     * Показать страницу регистрации.
     */
    public function showRegister()
    {
        return Inertia::render('Auth/Register');
    }

    /**
     * Регистрация нового пользователя.
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        // ✅ Данные уже валидны — берём через validated()
        $user = User::create([
            'name' => $request->validated('name'),
            'email' => $request->validated('email'),
            'password' => Hash::make($request->validated('password')),
        ]);

        // Создаём сессию для Inertia-страниц
        $this->authenticateUser($user, $request);

        return $this->jsonAuthResponse($user);
    }

    /**
     * Аутентификация пользователя.
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $user = $this->findAndVerifyUser(
            $request->validated('email'),
            $request->validated('password')
        );

        $this->authenticateUser($user, $request);

        return $this->jsonAuthResponse($user);
    }

    /**
     * Выход из системы.
     */
    public function logout(Request $request): \Illuminate\Http\RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Удаляем текущий токен, если он есть
        $request->user()?->currentAccessToken()?->delete();

        return redirect('/');
    }

    // === Приватные вспомогательные методы ===

    /**
     * Найти и проверить пользователя.
     *
     * @throws ValidationException
     */
    private function findAndVerifyUser(string $email, string $password): User
    {
        $user = User::where('email', $email)->first();

        if (!$user || !Hash::check($password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Предоставленные учетные данные неверны.'],
            ]);
        }

        return $user;
    }

    /**
     * Аутентифицировать пользователя (создать сессию).
     */
    private function authenticateUser(User $user, Request $request): void
    {
        Auth::login($user);
        $request->session()->regenerate();
    }

    /**
     * Сформировать ответ с токеном и данными пользователя.
     */
    private function jsonAuthResponse(User $user): JsonResponse
    {
        return response()->json([
            'token' => $user->createToken('admin-token')->plainTextToken,
            'user' => $user->only(['id', 'name', 'email']),
        ]);
    }
}
