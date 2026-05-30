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
        $this->ensureStaffUser(
            email: 'soambosne@gmail.com',
            name: 'Administrator',
            role: User::ROLE_ADMINISTRATOR,
        );

        $this->ensureStaffUser(
            email: 'editor@kurokami.com',
            name: 'Editor',
            role: User::ROLE_EDITOR,
        );

        $this->ensureStaffUser(
            email: 'subscriber@kurokami.com',
            name: 'Subscriber',
            role: User::ROLE_SUBSCRIBER,
        );
    }

    private function ensureStaffUser(string $email, string $name, string $role): void
    {
        $user = User::query()->firstOrNew(['email' => $email]);

        $user->name = $name;
        $user->role = $role;

        if (! $user->exists || blank($user->password)) {
            $user->password = 'password';
        }

        $user->save();
    }
}
