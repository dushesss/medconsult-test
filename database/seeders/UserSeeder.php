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
            [
                'name' => 'Админ',
                'email' => 'admin@test.local',
                'phone' => '+7 900 100-00-01',
                'birth_date' => '1985-03-15',
                'avatar_path' => null,
            ],
            [
                'name' => 'Иван Петров',
                'email' => 'ivan@test.local',
                'phone' => '+7 900 200-22-33',
                'birth_date' => '1990-07-22',
                'avatar_path' => null,
            ],
            [
                'name' => 'Мария Сидорова',
                'email' => 'maria@test.local',
                'phone' => '+7 916 333-44-55',
                'birth_date' => '1995-11-08',
                'avatar_path' => null,
            ],
            [
                'name' => 'Врач Тестов',
                'email' => 'doctor@test.local',
                'phone' => '+7 495 123-45-67',
                'birth_date' => '1978-01-30',
                'avatar_path' => null,
            ],
            [
                'name' => 'Пациент Обычный',
                'email' => 'patient@test.local',
                'phone' => null,
                'birth_date' => '2001-05-01',
                'avatar_path' => null,
            ],
            [
                'name' => 'Елена Козлова',
                'email' => 'elena@test.local',
                'phone' => '+7 903 777-88-99',
                'birth_date' => '1988-12-19',
                'avatar_path' => null,
            ],
            [
                'name' => 'Дмитрий Новиков',
                'email' => 'dmitry@test.local',
                'phone' => '+7 925 111-22-00',
                'birth_date' => null,
                'avatar_path' => null,
            ],
        ];

        foreach ($rows as $row) {
            User::query()->updateOrCreate(
                ['email' => $row['email']],
                [
                    'name' => $row['name'],
                    'password' => self::DEV_PASSWORD,
                    'phone' => $row['phone'],
                    'birth_date' => $row['birth_date'],
                    'avatar_path' => $row['avatar_path'],
                ]
            );
        }
    }
}
