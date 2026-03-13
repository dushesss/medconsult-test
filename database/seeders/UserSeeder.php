<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

final class UserSeeder extends Seeder
{
    /** Пароль для всех тестовых записей (только dev). */
    private const string DEV_PASSWORD = 'secret';

    public function run(): void
    {
        $rows = [
            ['name' => 'Админ', 'email' => 'admin@test.local'],
            ['name' => 'Иван', 'email' => 'ivan@test.local'],
            ['name' => 'Мария', 'email' => 'maria@test.local'],
            ['name' => 'Врач', 'email' => 'doctor@test.local'],
            ['name' => 'Пациент', 'email' => 'patient@test.local'],
        ];

        foreach ($rows as $row) {
            User::query()->updateOrCreate(
                ['email' => $row['email']],
                [
                    'name' => $row['name'],
                    'password' => self::DEV_PASSWORD,
                ]
            );
        }
    }
}
