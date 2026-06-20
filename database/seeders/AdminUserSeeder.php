<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use RuntimeException;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $email = env('ADMIN_EMAIL', 'soambosne@gmail.com');
        $name = env('ADMIN_NAME', 'Administrator');
        $password = env('ADMIN_PASSWORD');

        if (blank($password)) {
            throw new RuntimeException(
                'ADMIN_PASSWORD is required. Set it in Laravel Cloud environment variables before running AdminUserSeeder.'
            );
        }

        User::query()->updateOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => $password,
                'role' => User::ROLE_ADMINISTRATOR,
                'email_verified_at' => now(),
            ],
        );
    }
}
