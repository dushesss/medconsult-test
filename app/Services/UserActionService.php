<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use App\Models\UserAction;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class UserActionService
{
    public const LEVEL_AUDIT = 'audit';

    public const LEVEL_TELEMETRY = 'telemetry';

    public function log(
        User $user,
        string $action,
        ?string $description = null,
        ?array $payload = null,
        ?string $ip = null,
        ?string $userAgent = null,
        string $level = self::LEVEL_AUDIT
    ): void {
        UserAction::query()->create([
            'user_id' => $user->id,
            'action' => $action,
            'level' => $level,
            'description' => $description,
            'payload' => $payload,
            'ip' => $ip,
            'user_agent' => $userAgent,
        ]);
    }

    /**
     * @return LengthAwarePaginator<int, UserAction>
     */
    public function paginateForUser(User $user, int $perPage, bool $includeTelemetry = false): LengthAwarePaginator
    {
        $perPage = min(max($perPage, 1), 100);

        $q = $user->userActions()->latest('created_at');
        if (! $includeTelemetry) {
            $q->where('level', self::LEVEL_AUDIT);
        }

        return $q->paginate($perPage);
    }
}
