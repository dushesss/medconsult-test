<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\UserAction;
use Illuminate\Console\Command;

final class PruneTelemetryUserActionsCommand extends Command
{
    protected $signature = 'user-actions:prune-telemetry
                            {--days= : Дней, старше которых удалять (по умолчанию из config)}';

    protected $description = 'Удаляет старые записи user_actions с level=telemetry';

    public function handle(): int
    {
        $days = (int) ($this->option('days') ?: config('medconsult.user_actions_telemetry_retention_days', 14));
        if ($days < 1) {
            $this->error('days должен быть >= 1');

            return self::FAILURE;
        }

        $cutoff = now()->subDays($days);
        $deleted = UserAction::query()
            ->where('level', 'telemetry')
            ->where('created_at', '<', $cutoff)
            ->delete();

        $this->info('Удалено записей telemetry: '.$deleted);

        return self::SUCCESS;
    }
}
