<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use App\Models\UserFile;

final class UserFilePolicy
{
    public function view(User $user, UserFile $userFile): bool
    {
        return $user->id === $userFile->user_id;
    }

    public function delete(User $user, UserFile $userFile): bool
    {
        return $user->id === $userFile->user_id;
    }
}
