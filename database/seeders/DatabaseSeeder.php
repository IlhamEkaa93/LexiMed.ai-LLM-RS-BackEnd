<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // KITA NONAKTIFKAN TEST USER BAWAAN LARAVEL
        // \App\Models\User::factory(10)->create();
        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        // KITA PANGGIL SEEDER KUSTOM KITA
        $this->call([
            RoleAndUserSeeder::class,
        ]);
    }
}