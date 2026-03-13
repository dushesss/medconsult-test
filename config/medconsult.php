<?php

declare(strict_types=1);

return [
    /*
     * Суммарный лимит размера файлов на пользователя (байты). 0 = без лимита.
     */
    'user_files_quota_bytes' => (int) env('USER_FILES_QUOTA_BYTES', 524_288_000),

    /*
     * Сколько дней хранить записи user_actions с level=telemetry (команда prune).
     */
    'user_actions_telemetry_retention_days' => (int) env('USER_ACTIONS_TELEMETRY_RETENTION_DAYS', 14),
];
