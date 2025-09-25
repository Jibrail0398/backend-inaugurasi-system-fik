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
            'name' => env("SEED_ADMIN_NAME"),
            'email' => env("SEED_ADMIN_EMAIL"),
            'nim' => env("SEED_ADMIN_NIM"),
            'password' => Hash::make(env("SEED_ADMIN_PASSWORD")),
            'role' => env("SEED_ADMIN_ROLE"),
        ]);

        // Mentor
        User::create([
            'name' => env("SEED_MENTOR_NAME"),
            'email' => env("SEED_MENTOR_EMAIL"),
            'nim' => env("SEED_MENTOR_NIM"),
            'password' => Hash::make(env("SEED_MENTOR_PASSWORD")),
            'role' => env("SEED_MENTOR_ROLE"),
        ]);



    }

    
}
