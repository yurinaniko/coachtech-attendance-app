<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run()
    {
        User::create([
            'name' => '管理者',
            'email' => 'admin@example.com',
            'email_verified_at' => now(),
            'password' => Hash::make('password123'),
            'is_admin' => true,
        ]);

        User::factory()->count(10)->create([
            'password' => Hash::make('password'),
            'is_admin' => false,
        ]);
    }
}
