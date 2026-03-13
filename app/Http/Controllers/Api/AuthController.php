<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\LoginRequest;
use App\Http\Requests\Api\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Http\Responses\ApiResponse;
use App\Services\AuthService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class AuthController extends Controller
{
    public function __construct(
        private readonly AuthService $authService
    ) {}

    public function login(LoginRequest $request): JsonResponse
    {
        $result = $this->authService->login(
            $request->validated('email'),
            $request->validated('password'),
            $request->ip(),
            $request->userAgent()
        );

        if ($result === null) {
            return ApiResponse::error('Неверный email или пароль', null, 401);
        }

        if (isset($result['needs_verification'])) {
            return ApiResponse::error(
                'Подтвердите email по ссылке из письма. Можно запросить письмо снова: POST /api/v1/email/verification-notification с токеном после регистрации.',
                null,
                403
            );
        }

        return ApiResponse::success(
            [
                'user' => (new UserResource($result['user']))->resolve(),
                'token' => $result['token'],
                'token_type' => 'Bearer',
            ],
            'Вход выполнен'
        );
    }

    public function register(RegisterRequest $request): JsonResponse
    {
        $result = $this->authService->register(
            $request->validated('name'),
            $request->validated('email'),
            $request->validated('password'),
            $request->ip(),
            $request->userAgent()
        );

        event(new Registered($result['user']));

        return ApiResponse::success(
            [
                'user' => (new UserResource($result['user']))->resolve(),
                'token' => $result['token'],
                'token_type' => 'Bearer',
                'hint' => 'Подтвердите email по ссылке из письма, затем войдите снова (токен до подтверждения только для повторной отправки письма).',
            ],
            'Регистрация успешна',
            201
        );
    }

    public function logout(Request $request): JsonResponse
    {
        if ($request->user() === null) {
            return ApiResponse::error('Не авторизован', null, 401);
        }

        $this->authService->logout(
            $request->user(),
            $request->bearerToken(),
            $request->ip(),
            $request->userAgent()
        );

        return ApiResponse::success(null, 'Выход выполнен');
    }

    public function sendVerification(Request $request): JsonResponse
    {
        $user = $request->user();
        if ($user === null) {
            return ApiResponse::error('Не авторизован', null, 401);
        }
        if ($user->hasVerifiedEmail()) {
            return ApiResponse::success(null, 'Email уже подтверждён');
        }

        $user->sendEmailVerificationNotification();

        return ApiResponse::success(null, 'Письмо с ссылкой отправлено');
    }
}
