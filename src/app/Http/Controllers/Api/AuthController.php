<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Аутентификация
 */
class AuthController extends Controller
{
    /**
     * Показать страницу входа.
     */
    public function showLogin(): Response
    {
        return Inertia::render('Auth/Login');
    }

    /**
     * Показать страницу регистрации.
     */
    public function showRegister(): Response
    {
        return Inertia::render('Auth/Register');
    }

    /**
     * Регистрация нового пользователя.
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $user = User::create([
            'name' => $request->validated('name'),
            'email' => $request->validated('email'),
            'password' => Hash::make($request->validated('password')),
        ]);

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

        return $this->jsonAuthResponse($user);
    }

    /**
     * Выход из системы.
     */
    public function logout(Request $request)
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        $request->user()?->currentAccessToken()?->delete();

        if ($request->expectsJson() || $request->header('X-Inertia')) {
            return response()->json(['message' => 'Успешный выход']);
        }

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
