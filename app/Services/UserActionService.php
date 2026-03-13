<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use App\Models\UserAction;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class UserActionService
{
    public function log(
        User $user,
        string $action,
        ?string $description = null,
        ?array $payload = null,
        ?string $ip = null,
        ?string $userAgent = null
    ): void {
        UserAction::query()->create([
            'user_id' => $user->id,
            'action' => $action,
            'description' => $description,
            'payload' => $payload,
            'ip' => $ip,
            'user_agent' => $userAgent,
        ]);
    }

    /**
     * @return LengthAwarePaginator<int, UserAction>
     */
    public function paginateForUser(User $user, int $perPage): LengthAwarePaginator
    {
        $perPage = min(max($perPage, 1), 100);

        return $user->userActions()
            ->latest('created_at')
            ->paginate($perPage);
    }
}
