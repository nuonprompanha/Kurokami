<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::query()->updateOrCreate(
            ['email' => 'soambosne@gmail.com'],
            [
                'name' => 'Administrator',
                'password' => 'password',
                'role' => User::ROLE_ADMINISTRATOR,
            ]
        );

        User::query()->updateOrCreate(
            ['email' => 'editor@kurokami.com'],
            [
                'name' => 'Editor',
                'password' => 'password',
                'role' => User::ROLE_EDITOR,
            ]
        );

        User::query()->updateOrCreate(
            ['email' => 'subscriber@kurokami.com'],
            [
                'name' => 'Subscriber',
                'password' => 'password',
                'role' => User::ROLE_SUBSCRIBER,
            ]
        );
    }
}
