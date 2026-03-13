<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\LoginRequest;
use App\Http\Requests\Api\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Http\Responses\ApiResponse;
use App\Services\AuthService;
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
            $request->validated('password')
        );

        if ($result === null) {
            return ApiResponse::error('Неверный email или пароль', null, 401);
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
            $request->validated('password')
        );

        return ApiResponse::success(
            [
                'user' => (new UserResource($result['user']))->resolve(),
                'token' => $result['token'],
                'token_type' => 'Bearer',
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

        $this->authService->revokeBearerToken($request->bearerToken());

        return ApiResponse::success(null, 'Выход выполнен');
    }
}
