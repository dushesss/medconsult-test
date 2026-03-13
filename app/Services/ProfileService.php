<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;

final readonly class ProfileService
{
    public function __construct(
        private UserActionService $userActionService
    ) {}

    /**
     * @param  array{name?: string, email?: string, phone?: string|null, birth_date?: string|null}  $data
     */
    public function update(User $user, array $data, string $ip, ?string $userAgent): User
    {
        if (array_key_exists('name', $data)) {
            $user->name = $data['name'];
        }
        if (array_key_exists('email', $data)) {
            $user->email = $data['email'];
        }
        if (array_key_exists('phone', $data)) {
            $user->phone = $data['phone'];
        }
        if (array_key_exists('birth_date', $data)) {
            $user->birth_date = $data['birth_date'];
        }

        $user->save();

        $this->userActionService->log(
            $user,
            'profile_update',
            null,
            array_keys($data),
            $ip,
            $userAgent
        );

        return $user->fresh() ?? $user;
    }
}
