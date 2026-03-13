<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\PersonalAccessToken;

final class AuthService
{
    private const string TOKEN_NAME = 'api';

    /**
     * @return array{user: User, token: string}|null
     */
    public function login(string $email, string $password): ?array
    {
        if (! Auth::attempt(['email' => $email, 'password' => $password])) {
            return null;
        }

        $user = Auth::user();
        if (! $user instanceof User) {
            return null;
        }

        $token = $user->createToken(self::TOKEN_NAME)->plainTextToken;

        return ['user' => $user, 'token' => $token];
    }

    /**
     * @return array{user: User, token: string}
     */
    public function register(string $name, string $email, string $password): array
    {
        $user = User::query()->create([
            'name' => $name,
            'email' => $email,
            'password' => $password,
        ]);

        $token = $user->createToken(self::TOKEN_NAME)->plainTextToken;

        return ['user' => $user, 'token' => $token];
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
