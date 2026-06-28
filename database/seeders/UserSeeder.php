<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $adminRole = \App\Models\Role::where('slug', 'super-admin')->first();

        User::updateOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'name' => 'waleed',
                'username' => 'admin',
                'password' => Hash::make('password'),
                'usertype' => 'admin',
                'role_id' => $adminRole?->id,
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );

        $userRole = \App\Models\Role::where('slug', 'admin')->first();

        User::updateOrCreate(
            ['email' => 'user@gmail.com'],
            [
                'name' => 'Test User',
                'username' => 'testuser',
                'password' => Hash::make('password'),
                'usertype' => 'user',
                'role_id' => $userRole?->id,
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );
    }
}