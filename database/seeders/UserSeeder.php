<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */


    // Gunakan seeder ini untuk membuat user admin dan mentor awal untuk testing
    // php artisan db:seed --class=UserSeeder
    // atau bisa pakai php artisan migrate:fresh --seed
    public function run(): void
    {
        // Admin
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'NIM' => '1234567890',
            'password' => Hash::make('password123'),
            'role' => 'admin',
        ]);

        // Mentor
        User::create([
            'name' => 'Mentor User',
            'email' => 'mentor@example.com',
            'NIM' => '1234567891',
            'password' => Hash::make('password123'),
            'role' => 'mentor',
        ]);

    }
}
