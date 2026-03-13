<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\PersonalAccessToken;

final class AuthService
{
    private const string TOKEN_NAME = 'api';

    public function __construct(
        private readonly UserActionService $userActionService
    ) {}

    /**
     * @return array{user: User, token: string}|null
     */
    public function login(
        string $email,
        string $password,
        string $ip,
        ?string $userAgent
    ): ?array {
        if (! Auth::attempt(['email' => $email, 'password' => $password])) {
            return null;
        }

        $user = Auth::user();
        if (! $user instanceof User) {
            return null;
        }

        $token = $user->createToken(self::TOKEN_NAME)->plainTextToken;

        $this->userActionService->log($user, 'login', null, null, $ip, $userAgent);

        return ['user' => $user, 'token' => $token];
    }

    /**
     * @return array{user: User, token: string}
     */
    public function register(
        string $name,
        string $email,
        string $password,
        string $ip,
        ?string $userAgent
    ): array {
        $user = User::query()->create([
            'name' => $name,
            'email' => $email,
            'password' => $password,
        ]);

        $token = $user->createToken(self::TOKEN_NAME)->plainTextToken;

        $this->userActionService->log($user, 'register', null, null, $ip, $userAgent);

        return ['user' => $user, 'token' => $token];
    }

    public function logout(User $user, ?string $plainToken, string $ip, ?string $userAgent): void
    {
        $this->userActionService->log($user, 'logout', null, null, $ip, $userAgent);
        $this->revokeBearerToken($plainToken);
    }

    public function revokeBearerToken(?string $plainToken): void
    {
        if ($plainToken === null || $plainToken === '') {
            return;
        }

        $accessToken = PersonalAccessToken::findToken($plainToken);
        $accessToken?->delete();
    }
}
