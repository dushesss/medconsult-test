<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;

final readonly class ProfileService
{
    /**
     * @param  array{name?: string, email?: string, phone?: string|null, birth_date?: string|null}  $data
     */
    public function update(User $user, array $data): User
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

        return $user->fresh() ?? $user;
    }
}
