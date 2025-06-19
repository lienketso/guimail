<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        \App\Models\User::create([
            'name' => 'Admin',
            'email' => 'thanhan1507@gmail.com',
            'password' => bcrypt('123456'),
            'role' => 'admin',
        ]);
        \App\Models\User::create([
            'name' => 'User',
            'email' => 'user@demo.com',
            'password' => bcrypt('123456'),
            'role' => 'user',
        ]);
    }
}
